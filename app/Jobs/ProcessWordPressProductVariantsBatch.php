<?php

namespace App\Jobs;

use App\Models\DigitalProductVariation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FirebaseNotificationTrait;

class ProcessWordPressProductVariantsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch;
    public $productIds;

    public function __construct(array $productIds,$page, $perPage = 10, $isLastBatch = false)
    {
        $this->productIds = $productIds;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
        try {
            foreach ($this->productIds as $productId) {
                $this->syncProductVariantsForProduct($productId);
            }

            if ($this->isLastBatch) {
                $this->syncDeletedProductVariants();
            }

        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getWordPressProductsBatch(array $productIds)
    {
        // $response = Http::withBasicAuth(
        //     env('CONSUMER_KEY'),
        //     env('CONSUMER_SECRET')
        // )->timeout(60)->get('https://maximfood.com/wp-json/wc/v3/products', [
        //     'per_page' => $this->perPage,
        //     'page' => $this->page,
        //     'orderby' => 'date',
        //     'order' => 'desc'
        // ]);

        // if (!$response->successful()) {
        //     Log::error("Failed to fetch WordPress products batch", [
        //         'page' => $this->page,
        //         'status' => $response->status(),
        //         'response' => $response->body()
        //     ]);
        //     return [];
        // }

        // return $response->json();



            $successfulIds = [];
            $failedIds = [];

            foreach ($productIds as $productId) {
                Log::info("Fetching product details for ID: {$productId}");
//                try {
                    $response = Http::withBasicAuth(
                        env('CONSUMER_KEY'),
                        env('CONSUMER_SECRET')
                    )
                    ->timeout(30)
                    ->retry(3, 1000) // Retry 3 times with 1 second delay
                    ->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/products/{$productId}");

                    if ($response->successful()) {
                        $successfulIds[$productId] = $response->json();
                    } else {
                        $failedIds[$productId] = $response->status();

                        // Log specific error details
                        Log::warning("Failed to fetch product", [
                            'product_id' => $productId,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);

                        // Implement exponential backoff
                        sleep(min(pow(2, count($failedIds)), 60)); // Max 60 seconds
                    }
//                } catch (\Exception $e) {
//                    $failedIds[$productId] = $e->getMessage();
//                    Log::error("Exception fetching product", [
//                        'product_id' => $productId,
//                        'error' => $e->getMessage()
//                    ]);
//                }
            }

            return [
                'successful' => $successfulIds,
                'failed' => $failedIds
            ];
    }

    private function syncProductVariantsForProduct($productId)
    {
        $page = 1;
        $perPage = 100;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/products/{$productId}/variations", [
                'per_page' => $perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->successful()) {

                $productVariants = $response->json();
                if ($productId  == '13598'){
                    Log::info("my variant", [
                        'product_id' => $productId,
                        'variants' => $productVariants
                    ]);
                }
                Log::info("Processing variants for product", [
                    'product_id' => $productId,
                    'page' => $page,
                    'variants_count' => count($productVariants)
                ]);

                foreach ($productVariants as $productVariant) {
//                    try {
                        $productVariantRecord = DigitalProductVariation::updateOrCreate(
                            ['wordpress_id' => $productVariant['id']],
                            [
                                'product_id' => $productId, // Make sure to store the parent product ID
                                'sku' => $productVariant['sku'],
                                'description' => $productVariant['description'] ?? '',
                                'price' => $productVariant['price'] ?? 0,
                                'regular_price' => (float) $productVariant['regular_price'] ?? 0,
                                'sale_price' => (float) $productVariant['on_sale'] == 1 ? (float) $productVariant['sale_price'] : (float) $productVariant['regular_price'],
                                'date_on_sale_from' => $productVariant['date_on_sale_from'] ?? null,
                                'date_on_sale_to' => $productVariant['date_on_sale_to'] ?? null,
                                'on_sale' => $productVariant['on_sale'] ?? false,
                                'tax_status' => $productVariant['tax_status'] ?? '',
                                'stock_status' => $productVariant['stock_status'] ?? '',
                                'image' => $productVariant['image']['src'] ?? null,
//                                'image' => json_encode($productVariant['images']['src'] ?? []),

                                'attributes' => json_encode($productVariant['attributes'] ?? []),
                                'menu_order' => $productVariant['menu_order'] ?? 0,
                            ]
                        );
                    if ($productVariant && DigitalProductVariation::where('wordpress_id', $productVariant['id'])->first()->is_notified==false) {
                        $additionalData = [
                            'refrence_id' => $productVariantRecord->id,
                            'refrence_type'=>'product_variant',
                        ];
                        $data = [
                            "title" => request('title', "new product variant has been added" ),
                            "body" => request('body', "product variant has been added"),
                        ];
                        $user_ids = User::pluck('id')->toArray();
                        // $this->sendFcm($data, $user_ids, $additionalData);

                        DigitalProductVariation::where('wordpress_id', $productVariant['id'])->update(['is_notified' => true]);
                        //make sure to get all the product variants for the first time before uncommenting this section of code
                    }

                        Log::info("Processed variant", [
                            'variant_id' => $productVariant['id'],
                            'product_id' => $productId
                        ]);

//                    } catch (\Exception $e) {
//                        Log::error("Variant sync failed", [
//                            'product_id' => $productId,
//                            'variant_id' => $productVariant['id'],
//                            'error' => $e->getMessage()
//                        ]);
//                    }
                }

                $hasMore = count($productVariants) === $perPage;
                $page++;
            } else {
                Log::error("Failed to fetch variants for product", [
                    'product_id' => $productId,
                    'page' => $page,
                    'status' => $response->status()
                ]);
                $hasMore = false;
            }
        }
    }

    private function syncDeletedProductVariants()
    {
        try {
            Log::info("Starting deleted product variants sync in last batch");

            $wordpressVariantIds = $this->getAllWordPressVariantIds();

            if (empty($wordpressVariantIds)) {
                Log::warning("No WordPress variant IDs found for deletion sync");
                return;
            }

            $localVariants = DigitalProductVariation::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localVariants as $localVariant) {
                if (!in_array($localVariant->wordpress_id, $wordpressVariantIds)) {
                    DB::beginTransaction();

                    try {
                        ProductStock::where('product_id', $localVariant->id)->delete();

                        $localVariant->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted variant", [
                            'wordpress_id' => $localVariant->wordpress_id,
                            'local_id' => $localVariant->id
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete variant", [
                            'wordpress_id' => $localVariant->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted variants sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_variants' => count($wordpressVariantIds),
                'total_local_variants' => $localVariants->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted variants sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressVariantIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        $productIds = $this->getAllWordPressProductIds();

        foreach ($productIds as $productId) {
            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/products/{$productId}/variations", [
                    'per_page' => $perPage,
                    'page' => $page,
                    '_fields' => 'id'
                ]);

                if ($response->successful()) {
                    $variants = $response->json();

                    foreach ($variants as $variant) {
                        $allIds[] = $variant['id'];
                    }

                    $hasMore = count($variants) === $perPage;
                    $page++;

                    usleep(300000); // 0.3 seconds
                } else {
                    Log::error("Failed to fetch WordPress variants for product", [
                        'product_id' => $productId,
                        'page' => $page
                    ]);
                    $hasMore = false;
                }
            }
        }

        return $allIds;
    }

    private function getAllWordPressProductIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
                'per_page' => $perPage,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $products = $response->json();

                foreach ($products as $product) {
                    $allIds[] = $product['id'];
                }

                $page++;

                usleep(300000); // 0.3 seconds
            } else {
                Log::error("Failed to fetch WordPress products for ID sync", ['page' => $page]);
                break;
            }

        } while (count($products) === $perPage);

        return $allIds;
    }
}
