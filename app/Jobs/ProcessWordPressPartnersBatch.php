<?php

namespace App\Jobs;

// use App\Enums\ExportFileNames\Admin\Partner;
use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordPressPartnersBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        Log::info('before syncPartners', [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'isLastBatch' => $this->isLastBatch
        ]);

        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
        Log::info('ProcessWordPressPartnersBatch job created');
        try {
            Log::info('hello');
            // مزامنة المنتجات العادية
            $this->syncPartners();
            Log::info('after syncPartners', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedPartners();
            }


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncPartners()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/media-api/v1/list', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            // 'orderby' => 'date',
            // 'order' => 'desc'
        ]);

        Log::info($response->getBody()->getContents());

        if ($response->successful()) {
            $partners = $response->json()['data'];
//            dd($partners);

            Log::info("Processing batch", [
                'page' => $this->page,
                'partners_count' => count($partners),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($partners as $partner) {
                DB::beginTransaction();

                try {
                    // $partnerRecord = Partner::updateOrCreate(
                    Partner::create(
                        [
                            'image' => $partner,
//                            'slug' => $partner['slug'],
//                            'description' => $partner['description'] ?? '',
//                            'icon' => $partner['image'],
                        ]
                    );

                    // مزامنة المخزون
                    // ProductStock::updateOrCreate(
                    //     ['product_id' => $partnerRecord->id],
                    //     [
                    //         'sku' => $product['sku'] ?? '',
                    //         'price' => (float) ($product['price'] ?? 0),
                    //         'qty' => (int) ($product['stock_quantity'] ?? 0),
                    //     ]
                    // );

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Partner sync failed in batch", [
                        'page' => $this->page,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_partners' => count($partners) // تعديل هنا
            ]);
        }
    }

    private function syncDeletedPartners()
    {
        try {
            Log::info("Starting deleted partners sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressPartnerIds = $this->getAllWordPressPartnerIds();

            if (empty($wordpressPartnerIds)) {
                Log::warning("No WordPress partner IDs found for deletion sync");
                return;
            }

            // جلب الفئات المحلية
            $localPartners = Partner::all();

            $deletedCount = 0;

            foreach ($localPartners as $localPartner) {
                // إذا لم تعد الفئة موجودة في WordPress
//                if (!in_array($localPartner->wordpress_id,  $wordpressPartnerIds)) {
                    DB::beginTransaction();

                    try {
                        // حذف المخزون المرتبط أولاً
                        // ProductStock::where('product_id', $localPartner->id)->delete();

                        // حذف الفئة
                        $localPartner->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted partner", [
                            'wordpress_id' => $localPartner->wordpress_id,
                            'name' => $localPartner->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete partner", [
                            'wordpress_id' => $localPartner->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
//            }

            Log::info("Deleted partners sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_partners' => count($wordpressPartnerIds),
                'total_local_partners' => $localPartners->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted partners sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressPartnerIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/media-api/v1/list', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $partners = $response->json();

                foreach ($partners as $partner) {
                    $allIds[] = $partner['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress partners for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($partners) == 100);

        return $allIds;
    }
}
