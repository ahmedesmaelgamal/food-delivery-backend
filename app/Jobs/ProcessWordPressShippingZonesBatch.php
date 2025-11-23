<?php

namespace App\Jobs;

// use App\Enums\ExportFileNames\Admin\Category;
use App\Models\ShippingZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FirebaseNotificationTrait;

class ProcessWordPressShippingZonesBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels , FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        Log::info('before syncShippingZones', [
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
        Log::info('ProcessWordPressShippingZonesBatch job created');
        try {
            // مزامنة المنتجات العادية
            $this->syncShippingZones();
            // Log::info('after syncShippingZones', [
            //     'page' => $this->page,
            //     'perPage' => $this->perPage,
            //     'isLastBatch' => $this->isLastBatch
            // ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
//            if ($this->isLastBatch) {
//                $this->syncDeletedShippingZones();
//            }


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

//    private function syncShippingZones()
//    {
//        $response = Http::withBasicAuth(
//            env('CONSUMER_KEY'),
//            env('CONSUMER_SECRET')
//        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/shipping/zones', [
//            'per_page' => $this->perPage,
//            'page' => $this->page,
//            // 'orderby' => 'date',
//            // 'order' => 'desc'
//        ]);
//
//        Log::info($response->getBody()->getContents());
//
//        if ($response->successful()) {
//            $shippingZones = $response->json();
//
//            Log::info("Processing batch", [
//                'page' => $this->page,
//                'shipping_zones_count' => count($shippingZones),
//                'per_page' => $this->perPage,
//                'is_last_batch' => $this->isLastBatch
//            ]);
//
//            foreach ($shippingZones as $shippingZone) {
//                DB::beginTransaction();
//
//                try {
//                    $shippingZoneRecord = ShippingZone::updateOrCreate(
//                        ['wordpress_id' => $shippingZone['id']],
//                        [
//                            'name' => $shippingZone['name'],
//                            'order' => $shippingZone['order'],
//                        ]
//                    );
//
//
//                    if ($shippingZone && ShippingZone::where('wordpress_id', $shippingZone['id'])->first()->is_notified == false) {
//                        $additionalData = [
//                            'refrence_id' => $shippingZoneRecord->id,
//                            'refrence_type' => 'shipping_zone',
//                        ];
//                        $data = [
//                            "title" => request('title', "new shipping zone has been added"),
//                            "body" => request('body', "shipping zone has been added"),
//                        ];
//                        $user_ids = User::pluck('id')->toArray();
//                        // $this->sendFcm($data, $user_ids, $additionalData);
//                        ShippingZone::where('wordpress_id', $shippingZone['id'])->update(['is_notified' => true]);
//                        //make sure to get all the shipping zone for the first time before uncommenting this section of code
//                    }
//
//                    DB::commit();
//
//                } catch (\Exception $e) {
//                    DB::rollBack();
//                    Log::error("Shipping zone sync failed in batch", [
//                        'page' => $this->page,
//                        'shipping_zone_id' => $shippingZone['id'],
//                        'error' => $e->getMessage()
//                    ]);
//                }
//            }
//
//            Log::info("Batch completed successfully", [
//                'page' => $this->page,
//                'processed_shipping_zones' => count($shippingZone) // تعديل هنا
//            ]);
//        }
//    }




    private function syncShippingZones()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/shipping/zones');

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error('WordPress API connection failed (shipping-zones)');
            return 1;
        }

        $shippingZones = $response->json();
        $totalZones = count($shippingZones);

        if ($totalZones === 0) {
            $this->warn('No shipping zones found in WordPress');
            Log::warning('No shipping zones returned from API');
            return 1;
        }

        $this->info("Found {$totalZones} shipping zones");
        Log::info("Dispatching 1 batch job (shipping-zones)", ['count' => $totalZones]);

        // Dispatch only one batch since it’s all zones in one response
        ProcessWordPressShippingZonesBatch::dispatch(1, $totalZones, true);

        $this->info('All batches dispatched successfully!');
        Log::info('All shipping zone batch jobs dispatched', ['total_batches' => 1]);

        return 0;
    }

    private function syncDeletedShippingZones()
    {
        try {
            Log::info("Starting deleted shipping zones sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressShippingZoneIds = $this->getAllWordPressShippingZoneIds();

            if (empty($wordpressShippingZoneIds)) {
                Log::warning("No WordPress shipping zone IDs found for deletion sync");
                return;
            }

            // جلب الفئات المحلية
            $localShippingZones = ShippingZones::select('id', 'wordpress_id')->get();

            $deletedCount = 0;

            foreach ($localShippingZones as $localShippingZone) {
                // إذا لم تعد الفئة موجودة في WordPress
                if (!in_array($localShippingZone->wordpress_id, $wordpressShippingZoneIds)) {
                    DB::beginTransaction();

                    try {
                        // حذف المنتجات المرتبطة أولاً
                        ShippingZone::where('shipping_zone_id', $localShippingZone->id)->delete();

                        // حذف الفئة
                        $localShippingZone->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted shipping zone and associated products", [
                            'wordpress_id' => $localShippingZone->wordpress_id,
                            'name' => $localShippingZone->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete shipping zone and associated products", [
                            'wordpress_id' => $localShippingZone->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Log::info("Deleted shipping zones sync completed", [
            //     'deleted_count' => $deletedCount,
            //     'total_wordpress_shipping_zones' => count($wordpressCategoryIds),
            //     'total_local_shipping_zones' => $localShippingZones->count()
            // ]);

        } catch (\Exception $e) {
            Log::error("Deleted shipping zone sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressShippingZoneIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/shipping/zones', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $shippingZones = $response->json();

                foreach ($shippingZones as $shippingZone) {
                    $allIds[] = $shippingZone['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress shipping zones for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($shippingZone) == 100);

        return $allIds;
    }
}
