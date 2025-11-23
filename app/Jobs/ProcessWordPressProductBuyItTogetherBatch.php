<?php

namespace App\Jobs;

use App\Models\BuyItTogether;
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

class ProcessWordPressProductBuyItTogetherBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch;
    public $woodmartFbtIds;

    public function __construct(array $woodmartFbtIds,$page, $perPage = 10, $isLastBatch = false)
    {
        $this->woodmartFbtIds = $woodmartFbtIds;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
//        try {
            foreach ($this->woodmartFbtIds as $woodmartFbtId) {
                $this->syncProductBuyItTogether($woodmartFbtId);
            }

            if ($this->isLastBatch) {
                $this->syncDeletedBuyItTogether();
            }

//        } catch (\Exception $e) {
//            Log::error("Batch job failed", [
//                'page' => $this->page,
//                'error' => $e->getMessage()
//            ]);
//            throw $e;
//        }
    }

    private function getWordPressProductsBatch(array $woodmartFbtIds)
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

            foreach ($woodmartFbtIds as $woodmartFbtId) {
//                try {
                    $response = Http::withBasicAuth(
                        env('CONSUMER_KEY'),
                        env('CONSUMER_SECRET')
                    )
                    ->timeout(30)
                    ->retry(3, 1000) // Retry 3 times with 1 second delay
                    ->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/products/{$woodmartFbtId}");

                    if ($response->successful()) {
                        $successfulIds[$woodmartFbtId] = $response->json();
                    } else {
                        $failedIds[$woodmartFbtId] = $response->status();

                        // Log specific error details
                        Log::warning("Failed to fetch woodmart fbt", [
                            'woodmart_fbt_value' => $woodmartFbtId,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);

                        // Implement exponential backoff
                        sleep(min(pow(2, count($failedIds)), 60)); // Max 60 seconds
                    }
//                } catch (\Exception $e) {
//                    $failedIds[$woodmartFbtId] = $e->getMessage();
//                    Log::error("Exception fetching woodmart fbt", [
//                        'woodmart_fbt_value' => $woodmartFbtId,
//                        'error' => $e->getMessage()
//                    ]);
//                }
            }

            return [
                'successful' => $successfulIds,
                'failed' => $failedIds
            ];
    }

//    private function syncProductBuyItTogether($woodmartFbtId)
//    {
////        dd($woodmartFbtId);
//        $page = 1;
//        $perPage = 100;
//        $hasMore = true;
//
//        while ($hasMore) {
//            $response = Http::withBasicAuth(
//                env('CONSUMER_KEY'),
//                env('CONSUMER_SECRET')
//            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/woodmart/v1/fbt/{$woodmartFbtId}", [
//                'per_page' => $perPage,
//                'page' => $page,
//                'orderby' => 'date',
//                'order' => 'desc'
//            ]);
//
//
//            if ($response->successful()) {
//                $buyItTogethers = $response->json();
////                dd($buyItTogethers);
//
//                Log::info("Processing buy it together", [
//                    'woodmart_fbt_value' => $woodmartFbtId,
//                    'page' => $page,
////                    'buy_it_together_count' => count($buyItTogethers)
//                ]);
////dd($buyItTogethers['data']['meta_data']['_woodmart_fbt_products']);
//                foreach ($buyItTogethers['data']['meta_data']['_woodmart_fbt_products'] as $buyItTogether) {
////                    $fbtProducts = (array) $buyItTogether['data']['meta_data']['_woodmart_fbt_products'];
////                    $secondProduct = array_values($fbtProducts)[1] ?? null;
//////                    dd($secondProduct);
////                    dd($buyItTogether);
////                    dd($buyItTogether['meta_data'][1]['value']);
////                    dd($buyItTogether['data']['meta_data']['_woodmart_main_products_discount']);
////                    try {
////                    dd($response->json());
////                    dd($response->json()['data'],$buyItTogether,json_encode(array_column($buyItTogether, 'id')));
//
//
//
////                    $metaData = (array) ($buyItTogether['data']['meta_data'] ?? []);
////                    $fbtProducts = (array) ($metaData['_woodmart_fbt_products'] ?? []);
//
//                    $woodmartFbtProductIds = [];
//                    $woodmartFbtProductDiscounts = [];
////dd($buyItTogether);
//// Safely extract IDs and discounts
////                    foreach ($fbtProducts as $key => $fbt) {
////                        foreach ($buyItTogether as $key => $fbt) {
//////                            dd($fbt);
////
//////                            $fbt = (array) $fbt;
////
//                        $woodmartFbtProductIds[] = $buyItTogether['id'] ?? "";
////                        dd($woodmartFbtProductIds);
//                        $woodmartFbtProductDiscounts[] = $buyItTogether['discount'] ?? "";
////                        dd(json_encode($woodmartFbtProductDiscounts), json_encode($woodmartFbtProductIds));
////                    }
//
//                    $mainDiscount = $buyItTogethers['data']['meta_data']['_woodmart_main_products_discount'] ?? "";
//
//// Debug
////                    dd($mainDiscount, $buyItTogether, $woodmartFbtProductIds, $woodmartFbtProductDiscounts);
//
//
////                    dd(json_encode($woodmartFbtProductIds),json_encode($woodmartFbtProductDiscounts));
////                    dd(json_encode($woodmartFbtProductIds));
//                    $buyItTogetherRecord = BuyItTogether::updateOrCreate(
//
//                            ['wordpress_id' => $response->json()['data']['id']],
////                            [
//////                                'sku' => $buyItTogether['sku'],
//////                                'description' => $buyItTogether['description'] ?? '',
//////                                'price' => $buyItTogether['price'] ?? 0,
//////                                'regular_price' => (float) $buyItTogether['regular_price'] ?? 0,
//////                                'sale_price' => (float) $buyItTogether['on_sale'] == 1 ? (float) $buyItTogether['sale_price'] : (float) $buyItTogether['regular_price'],
//////                                'date_on_sale_from' => $buyItTogether['date_on_sale_from'] ?? null,
//////                                'date_on_sale_to' => $buyItTogether['date_on_sale_to'] ?? null,
//////                                'on_sale' => $buyItTogether['on_sale'] ?? false,
//////                                'tax_status' => $buyItTogether['tax_status'] ?? '',
//////                                'stock_status' => $buyItTogether['stock_status'] ?? '',
//////                                'image' => $buyItTogether['image']['src'] ?? null,
////////                              'image' => json_encode($buyItTogether['images']['src'] ?? []),
//////
//////                                'attributes' => json_encode($buyItTogether['attributes'] ?? []),
//////                                'woodmart_fbt_bundles_id' => $buyItTogether['meta_data'][1]['value'],
//////                                'menu_order' => $buyItTogether['menu_order'] ?? 0,
////
////
////
//////                            $table->unsignedBigInteger('wordpress_id');
////    //                        $table->boolean('is_notified')->default(false);
////    //                        $table->string('title');
////    //                        $table->string('woodmart_main_products_discount');
////    //                        $table->string('woodmart_fbt_products');
////    //                        $table->string('woodmart_fbt_discounts');
////    //                        $table->string('status');
////                                'wordpress_id'=>$response->json()['data']['id'],
////                                'is_notified'=>false,
////                                'title'=>$response->json()['data']['title'],
////                                'woodmart_main_products_discount'=>$response->json()['data']['meta_data']['_woodmart_main_products_discount'] ?: null,
//////                                'woodmart_fbt_products'=>$response->json()['data']['meta_data']['_woodmart_main_products_discount']['0']?:null,
//////                                'woodmart_fbt_discounts'=>$response->json()['image']['src'],
//////                                'woodmart_fbt_product_id'=>$buyItTogether['id'] ?: null,
//////                                'woodmart_fbt_product_discount'=>$buyItTogether['discount'] ?: null,
////
////                                'woodmart_fbt_product_ids' => json_encode(array_column($buyItTogetherProducts, 'id')),
////                                'woodmart_fbt_product_discounts' => json_encode(array_column($buyItTogetherProducts, 'discount')),
////
////                                'status'=>$response->json()['data']['status'],
////                            ]
//
//                            [
//                                'wordpress_id'=>$response->json()['data']['id'],
//                                'title' => $response->json()['data']['id'],
//                                'status' => $response->json()['data']['status'],
//    //                            'status' => $buyItTogether['status'] === 'publish' ? 1 : 0,
//                                'is_notified'=>false,
//                                'woodmart_main_products_discount' => $mainDiscount,
//                                'woodmart_fbt_product_id' => json_encode($woodmartFbtProductIds),
//                                'woodmart_fbt_product_discount' => json_encode($woodmartFbtProductDiscounts),
//                            ]
//                        );
//                    if ($buyItTogether && BuyItTogether::where('wordpress_id', $response->json()['data']['id'])->first()->is_notified==false) {
//                        $additionalData = [
//                            'refrence_id' => $buyItTogetherRecord->id,
//                            'refrence_type'=>'buy_it_together',
//                        ];
//                        $data = [
//                            "title" => request('title', "new buy it together has been added" ),
//                            "body" => request('body', "buy it together has been added"),
//                        ];
//                        $user_ids = User::pluck('id')->toArray();
//                        // $this->sendFcm($data, $user_ids, $additionalData);
//
//                        BuyItTogether::where('wordpress_id', $response->json()['data']['id'])->update(['is_notified' => true]);
//                        //make sure to get all the woodmart fbt for the first time before uncommenting this section of code
//                    }
//
//                        Log::info("Processed buy it together", [
//                            'buy_it_together_id' => $response->json()['data']['id'],
//                            'woodmart_fbt_value' => $woodmartFbtId
//                        ]);
//
////                    } catch (\Exception $e) {
////                        Log::error("buy it together sync failed", [
////                            'woodmart_fbt_value' => $woodmartFbtId,
////                            'buy_it_together_id' => $response->json()['data']['id'],
////                            'error' => $e->getMessage()
////                        ]);
////                    }
//                }
//
//                $hasMore = count($buyItTogethers) === $perPage;
//                $page++;
//            } else {
//                Log::error("Failed to fetch buy it together for woodmart fbt", [
//                    'woodmart_fbt_value' => $woodmartFbtId,
//                    'page' => $page,
//                    'status' => $response->status()
//                ]);
//                $hasMore = false;
//            }
//        }
//    }






    private function syncProductBuyItTogether($woodmartFbtId)
    {
        $page = 1;
        $perPage = 100;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/woodmart/v1/fbt/{$woodmartFbtId}", [
                'per_page' => $perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->successful()) {
                $buyItTogethers = $response->json();

                Log::info("Processing buy it together", [
                    'woodmart_fbt_value' => $woodmartFbtId,
                    'page' => $page,
                ]);

                // Initialize arrays BEFORE the loop to collect ALL products
                $woodmartFbtProductIds = [];
                $woodmartFbtProductDiscounts = [];

                // Loop through all FBT products and collect their IDs and discounts
                foreach ($buyItTogethers['data']['meta_data']['_woodmart_fbt_products'] as $buyItTogether) {
                    $woodmartFbtProductIds[] = $buyItTogether['id'] ?? "";
                    $woodmartFbtProductDiscounts[] = $buyItTogether['discount'] ?? "";
                }

                // Get main discount
                $mainDiscount = $buyItTogethers['data']['meta_data']['_woodmart_main_products_discount'] ?? "";

                // Create or update the record with ALL collected products
                $buyItTogetherRecord = BuyItTogether::updateOrCreate(
                    ['wordpress_id' => $response->json()['data']['id']],
                    [
                        'wordpress_id' => $response->json()['data']['id'],
                        'title' => $response->json()['data']['title'],
                        'status' => $response->json()['data']['status'],
                        'is_notified' => false,
                        'woodmart_main_products_discount' => $mainDiscount,
                        'woodmart_fbt_product_id' => json_encode($woodmartFbtProductIds),
                        'woodmart_fbt_product_discount' => json_encode($woodmartFbtProductDiscounts),
                    ]
                );

                // Handle notification (only once per record, not per product)
                if ($buyItTogetherRecord && !$buyItTogetherRecord->is_notified) {
                    $additionalData = [
                        'refrence_id' => $buyItTogetherRecord->id,
                        'refrence_type' => 'buy_it_together',
                    ];
                    $data = [
                        "title" => request('title', "new buy it together has been added"),
                        "body" => request('body', "buy it together has been added"),
                    ];
                    $user_ids = User::pluck('id')->toArray();
                    // $this->sendFcm($data, $user_ids, $additionalData);

                    BuyItTogether::where('wordpress_id', $response->json()['data']['id'])
                        ->update(['is_notified' => true]);
                    //make sure to get all the woodmart fbt for the first time before uncommenting this section of code
                }

                Log::info("Processed buy it together", [
                    'buy_it_together_id' => $response->json()['data']['id'],
                    'woodmart_fbt_value' => $woodmartFbtId,
                    'products_count' => count($woodmartFbtProductIds)
                ]);

                $hasMore = count($buyItTogethers) === $perPage;
                $page++;
            } else {
                Log::error("Failed to fetch buy it together for woodmart fbt", [
                    'woodmart_fbt_value' => $woodmartFbtId,
                    'page' => $page,
                    'status' => $response->status()
                ]);
                $hasMore = false;
            }
        }
    }







    private function syncDeletedBuyItTogether()
    {
//        try {
            Log::info("Starting deleted buy it together sync in last batch");

            $wordpressBuyItTogetherIds = $this->getAllWordPressBuyItTogetherIds();

            if (empty($wordpressBuyItTogetherIds)) {
                Log::warning("No WordPress buy it together IDs found for deletion sync");
                return;
            }

            $localBuyItTogethers = BuyItTogether::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localBuyItTogethers as $localBuyItTogether) {
                if (!in_array($localBuyItTogether->wordpress_id, $wordpressBuyItTogetherIds)) {
                    DB::beginTransaction();

//                    try {
//                        ProductStock::where('woodmart_fbt_value', $localBuyItTogether->id)->delete();

                        $localBuyItTogether->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted buy it together", [
                            'wordpress_id' => $localBuyItTogether->wordpress_id,
                            'local_id' => $localBuyItTogether->id
                        ]);

//                    } catch (\Exception $e) {
//                        DB::rollBack();
//                        Log::error("Failed to delete buy it together", [
//                            'wordpress_id' => $localBuyItTogether->wordpress_id,
//                            'error' => $e->getMessage()
//                        ]);
//                    }
                }
            }

//            Log::info("Deleted buy it together sync completed", [
//                'deleted_count' => $deletedCount,
//                'total_wordpress_buy_it_together' => count($wordpressBuyItTogetherIds),
//                'total_local_buy_it_together' => $localBuyItTogethers->count()
//            ]);

//        } catch (\Exception $e) {
//            Log::error("Deleted buy it together sync failed", ['error' => $e->getMessage()]);
//        }
    }

    private function getAllWordPressBuyItTogetherIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        $woodmartFbtIds = $this->getAllWordPressProductFbtIds();

        foreach ($woodmartFbtIds as $woodmartFbtId) {
            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/woodmart/v1/fbt/{$woodmartFbtId}", [
                    'per_page' => $perPage,
                    'page' => $page,
                    '_fields' => 'id'
                ]);

                if ($response->successful()) {
                    $buyItTogethers = $response->json();

                    foreach ($buyItTogethers as $buyItTogether) {
                        $allIds[] = $buyItTogether['data']['id'];
                    }

                    $hasMore = count($buyItTogethers) === $perPage;
                    $page++;

                    usleep(300000); // 0.3 seconds
                } else {
                    Log::error("Failed to fetch WordPress buy it together for this woodmart fbt", [
                        'woodmart_fbt_value' => $woodmartFbtId,
                        'page' => $page
                    ]);
                    $hasMore = false;
                }
            }
        }

        return $allIds;
    }

    private function getAllWordPressProductFbtIds()
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
//                dd($products);
                foreach ($products as $product) {
                    $allIds[] = $product['id'];
                }

                $page++;

                usleep(300000); // 0.3 seconds
            } else {
                Log::error("Failed to fetch WordPress woodmart fbts for ID sync", ['page' => $page]);
                break;
            }

        } while (count($woodmartFbts) === $perPage);

        return $allIds;
    }
}
