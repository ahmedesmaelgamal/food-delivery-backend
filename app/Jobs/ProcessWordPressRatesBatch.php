<?php

namespace App\Jobs;

// use App\Enums\ExportFileNames\Admin\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Review;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordPressRatesBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة
    public $productIds;


    public function __construct(array $productIds,$page, $perPage = 10, $isLastBatch = false)
    {
        Log::info('before syncReviews', [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'isLastBatch' => $this->isLastBatch
        ]);

        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
        $this->productIds = $productIds;

    }

    public function handle()
    {
        Log::info('ProcessWordPressReviewsBatch job created');
        try {
            Log::info('hello');
            // مزامنة المنتجات العادية

            foreach ($this->productIds as $productId) {
                $this->syncReviews($productId);
            }

            $this->syncReviews($productId);
            Log::info('after syncReviews', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedReviews();
            }


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncReviews($productId)
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/products/reviews/{$productId}", [
            'per_page' => $this->perPage,
            'page' => $this->page,
            // 'orderby' => 'date',
            // 'order' => 'desc'
        ]);

        Log::info($response->getBody()->getContents());

        if ($response->successful()) {
            $rates = $response->json();

            Log::info("Processing batch", [
                'page' => $this->page,
                'rates_count' => count($rates),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($rates as $rate) {
                DB::beginTransaction();

                try {
                    // $rateRecord = Review::updateOrCreate(
                    Review::updateOrCreate(
                        ['wordpress_id' => $rate['id']],
                        [
                            'name' => $rate['name'],
                            'slug' => $rate['slug'],
                            'description' => $rate['description'] ?? '',
                            'icon' => $rate['image'],
                        ]
                    );

                    // مزامنة المخزون
                    // ProductStock::updateOrCreate(
                    //     ['product_id' => $rateRecord->id],
                    //     [
                    //         'sku' => $product['sku'] ?? '',
                    //         'price' => (float) ($product['price'] ?? 0),
                    //         'qty' => (int) ($product['stock_quantity'] ?? 0),
                    //     ]
                    // );

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Review sync failed in batch", [
                        'page' => $this->page,
                        'rate_id' => $rate['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_rates' => count($rates) // تعديل هنا
            ]);
        }
    }

    private function syncDeletedReviews()
    {

        try {
            Log::info("Starting deleted rates sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressReviewIds = $this->getAllWordPressReviewIds();

            if (empty($wordpressReviewIds)) {
                Log::warning("No WordPress rate IDs found for deletion sync");
                return;
            }

            // جلب الفئات المحلية
            $localReviews = Review::select('id', 'wordpress_id')->get();

            $deletedCount = 0;

            foreach ($localReviews as $localReview) {
                // إذا لم تعد الفئة موجودة في WordPress
                if (!in_array($localReview->wordpress_id,  $wordpressReviewIds)) {
                    DB::beginTransaction();

                    try {
                        // حذف المخزون المرتبط أولاً
                        // ProductStock::where('product_id', $localReview->id)->delete();

                        // حذف الفئة
                        $localReview->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted rate", [
                            'wordpress_id' => $localReview->wordpress_id,
                            'name' => $localReview->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete rate", [
                            'wordpress_id' => $localReview->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted rates sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_rates' => count($wordpressReviewIds),
                'total_local_rates' => $localReviews->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted rates sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressReviewIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products/rates', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $rates = $response->json();

                foreach ($rates as $rate) {
                    $allIds[] = $rate['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress rates for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($rates) == 100);

        return $allIds;
    }
}
