<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductStock;
use App\Traits\FirebaseNotificationTrait;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordPressProductsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels , FirebaseNotificationTrait;


    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
//        try {
            // مزامنة المنتجات العادية
            $this->syncProducts();

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedProducts();
            }

//        } catch (\Exception $e) {
//            Log::error("Batch job failed", [
//                'page' => $this->page,
//                'error' => $e->getMessage()
//            ]);
//            throw $e;
//        }
    }

    private function syncProducts()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            'orderby' => 'date',
            'order' => 'desc',
            'lang'=>'en'
        ]);
//        dd($response->successful());

        if ($response->successful()) {
            $products = $response->json();

            Log::info("Processing batch", [
                'page' => $this->page,
                'products_count' => count($products),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($products as $product) {

//                dd($product['meta_data'][1]['value']);
//                dd($data = collect($product['meta_data'])->get(1)['value']);
//                dd($value = collect($product['meta_data'])[1]);
                $woodmart_fbt_bundles_id=$value = collect($product['meta_data'])[1];
//                dd(collect($product['meta_data'])->firstWhere('key', 'woodmart_fbt_bundles_id')['value'] ?? 0);
//                dd(json_encode(collect($product['meta_data'])->firstWhere('key', 'woodmart_fbt_bundles_id')['value'] ?? []));
//                if ( $product['id'] == 13920){
//                    dd($product['id']);
//                }
                Log::info('product id : '. $product['id'].' product slug : '.$product['slug']);
                DB::beginTransaction();
//                dd($product['total_sales']);
//                try {
//                dd($product['related_ids']);
//                if ($product['status'] == 'publish'){
//                dd(htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'));
                if ($product['type']!= 'variation'){
                    $productRecord = Product::updateOrCreate(
                        ['wordpress_id' => $product['id']],
                        [
                            'name' => html_entity_decode($product['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
//                            'name' => htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'),
                            'slug' => $product['slug'],
                            'category_ids' => json_encode(array_column($product['categories'] ?? [], 'id')),
                            'details' => $product['description'] ?? '',
                            'description' => $product['short_description'] ?? '',
                            'unit_price' => (float) ($product['price'] ?? null),
                            'sale_price' => (float) ($product['sale_price'] ?? null),
                            'on_sale' =>  $product['on_sale']==true?1 : 0,
                            'regular_price' => $product['regular_price']?:$product['price'],
                            'purchase_price' => (float) ($product['regular_price'] ?? null),
                            'current_stock' => (int) ($product['stock_status']=='instock' ? 1 : 0),
                            'published' => $product['status'] === 'publish' ? 1 : 0,
                            'status' => $product['status'] === 'publish' && $product['catalog_visibility']== 'visible' ? 1 : 0,
                            'images' => json_encode($product['images'] ?? []),
                            'thumbnail' => $product['images'][0]['src'] ?? null,
                            'meta_title' => $product['name'],
                            'meta_description' => strip_tags($product['short_description'] ?? ''),
                            'refundable' => 1,
                            'is_taxable' => $product['tax_status'] == 'taxable' ? 1 : 0,
                            'product_type' => 'physical',
                            'date_on_sale_from' => isset($product['date_on_sale_from']) ? strtotime($product['date_on_sale_from']) : null,
                            'date_on_sale_to' => isset($product['date_on_sale_to']) ? strtotime($product['date_on_sale_to']) : null,
                            'woodmart_fbt_bundles_id' => json_encode(collect($product['meta_data'])->firstWhere('key', 'woodmart_fbt_bundles_id')['value'] ?? []),
                            'related_ids' => json_encode($product['related_ids']),
                            'lang'=>$product['lang'],
//                            'translation_ar'=>@$product['translations']['ar']??null,
//                            'translation_en'=>@$product['translations']['en']??null,

                            'total_sales' => (int) ($product['total_sales'] ?? 0),

                            'translation_ar' => isset($product['translations']['ar'])
                                ? (is_array($product['translations']['ar'])
                                    ? json_encode($product['translations']['ar'])
                                    : $product['translations']['ar'])
                                : null,

                            'translation_en' => isset($product['translations']['en'])
                                ? (is_array($product['translations']['en'])
                                    ? json_encode($product['translations']['en'])
                                    : $product['translations']['en'])
                                : null,
                        ]
                    );

                }

//                Log::info('product has been created successfully : '.$productRecord );
//                }



                    // if ($product && Product::where('wordpress_id', $product['id'])->first()->is_notified == false) {
                    //     $additionalData = [
                    //         'refrence_id' => $productRecord->id,
                    //         'refrence_type' => 'product',
                    //     ];
                    //     $data = [
                    //         "title" => request('title', "new product has been added"),
                    //         "body" => request('body', "product has been added"),
                    //     ];
                    //     $user_ids = User::pluck('id')->toArray();
                        // $this->sendFcm($data, $user_ids, $additionalData);
                        Product::where('wordpress_id', $product['id'])->update(['is_notified' => true]);
                        //make sure to get all the products for the first time before uncommenting this section of code
                    // }


                    // مزامنة المخزون
                    ProductStock::updateOrCreate(
                        ['product_id' => $productRecord->id],
                        [
                            'sku' => $product['sku'] ?? '',
                            'price' => (float) ($product['price'] ?? 0),
                            'qty' => (int) ($product['stock_quantity'] ?? 0),
                        ]
                    );

                    DB::commit();

//                } catch (\Exception $e) {
//                    DB::rollBack();
//                    Log::error("Product sync failed in batch", [
//                        'page' => $this->page,
//                        'product_id' => $product['id'],
//                        'error' => $e->getMessage()
//                    ]);
//                }

            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_products' => count($products)
            ]);
        }
    }


    private function syncDeletedProducts()
    {
//        try {
            Log::info("Starting deleted products sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressProductIds = $this->getAllWordPressProductIds();

            if (empty($wordpressProductIds)) {
                Log::warning("No WordPress product IDs found for deletion sync");
                return;
            }

            // جلب المنتجات المحلية
            $localProducts = Product::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localProducts as $localProduct) {
                // إذا لم يعد المنتج موجوداً في WordPress
                if (!in_array($localProduct->wordpress_id, $wordpressProductIds)) {
                    DB::beginTransaction();

//                    try {
                        // حذف المخزون المرتبط أولاً
                        ProductStock::where('product_id', $localProduct->id)->delete();

                        // حذف المنتج
                        $localProduct->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted product", [
                            'wordpress_id' => $localProduct->wordpress_id,
                            'name' => $localProduct->name
                        ]);

//                    } catch (\Exception $e) {
//                        DB::rollBack();
//                        Log::error("Failed to delete product", [
//                            'wordpress_id' => $localProduct->wordpress_id,
//                            'error' => $e->getMessage()
//                        ]);
//                    }
                }
            }

            Log::info("Deleted products sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_products' => count($wordpressProductIds),
                'total_local_products' => $localProducts->count()
            ]);

//        } catch (\Exception $e) {
//            Log::error("Deleted products sync failed", ['error' => $e->getMessage()]);
//        }
    }

    private function getAllWordPressProductIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $products = $response->json();

                foreach ($products as $product) {
                    $allIds[] = $product['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress products for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($products) == 100);

        return $allIds;
    }
}
