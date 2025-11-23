<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Jobs\ProcessWordPressCitiesBatch;
use App\Jobs\ProcessWordpressProductsInArabicBatch;
use App\Jobs\ProcessWordPressCouponsBatch;
use App\Jobs\ProcessWordPressCustomersBatch;
use App\Jobs\ProcessWordPressOrdersBatch;
use App\Jobs\ProcessWordPressPartnersBatch;
use App\Jobs\ProcessWordPressProductBuyItTogetherBatch;
use App\Jobs\ProcessWordPressProductsBatch;
use App\Jobs\ProcessWordPressProductVariantsBatch;
use App\Jobs\ProcessWordPressShippingZoneLocationsBatch;
use App\Jobs\ProcessWordPressShippingZoneMethodsBatch;
use App\Jobs\ProcessWordPressShippingZonesBatch;
use App\Jobs\ProcessWordpressBannerBatch;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class WordpressController extends Controller
{




    public function syncAllProductsInBatches(Request $request)
    {
//        try {
            $productsPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $totalProducts = $this->getTotalProductsCount();

            if ($totalProducts === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $totalPages = ceil($totalProducts / $productsPerPage);

            // إنشاء jobs
            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل

                ProcessWordPressProductsBatch::dispatch($page, $productsPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_products' => $totalProducts,
                    'total_pages' => $totalPages,
                    'products_per_page' => $productsPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
                ]
            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء المزامنة',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }



    public function syncAllProductsInArabicInBatches(Request $request)
    {
//        try {
        $productsPerPage = $request->get('per_page', 10);
        $delayBetweenJobs = $request->get('delay', 5);
        $syncDeleted = $request->get('sync_deleted', true);

        $totalProducts = $this->getTotalProductsCount();

        if ($totalProducts === 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد منتجات للمزامنة'
            ], 404);
        }

        $totalPages = ceil($totalProducts / $productsPerPage);

        // إنشاء jobs
        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل

            ProcessWordpressProductsInArabicBatch::dispatch($page, $productsPerPage, $isLastBatch)
                ->delay(now()->addSeconds($page * $delayBetweenJobs));
        }

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
            'sync_details' => [
                'total_products' => $totalProducts,
                'total_pages' => $totalPages,
                'products_per_page' => $productsPerPage,
                'sync_deleted' => $syncDeleted,
                'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
            ]
        ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء المزامنة',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }
    
    
    





    private function getTotalProductsCount()
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalProducts = $response->header('X-WP-Total');

                if ($totalProducts) {
                    return (int) $totalProducts;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalProductsAlternative();
            }

            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to get total products count", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function calculateTotalProductsAlternative()
    {
        try {
            $page = 1;
            $totalProducts = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $products = $response->json();
                    $totalProducts += count($products);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }
            } while (count($products) == 100);

            return $totalProducts;
        } catch (\Exception $e) {
            Log::error("Failed to calculate total products", ['error' => $e->getMessage()]);
            return 0;
        }
    }


    private function syncProductStocks($productRecord, $wpProduct)
    {
        // حذف المخزون القديم
        ProductStock::where('product_id', $productRecord->id)->delete();

        // إضافة المخزون الجديد
        if (!empty($wpProduct['variations'])) {
            // منتج له variations
            foreach ($wpProduct['variations'] as $variationId) {
                $variation = $this->getProductVariation($wpProduct['id'], $variationId);
                if ($variation) {
                    ProductStock::create([
                        'product_id' => $productRecord->id,
                        'variant' => $this->formatVariant($variation['attributes'] ?? []),
                        'sku' => $variation['sku'] ?? '',
                        'price' => (float) ($variation['price'] ?? 0),
                        'qty' => (int) ($variation['stock_quantity'] ?? 0),
                    ]);
                }
            }
        } else {
            // منتج بسيط بدون variations
            ProductStock::create([
                'product_id' => $productRecord->id,
                'variant' => null,
                'sku' => $wpProduct['sku'] ?? '',
                'price' => (float) ($wpProduct['price'] ?? 0),
                'qty' => (int) ($wpProduct['stock_quantity'] ?? 0),
            ]);
        }
    }

    private function getProductVariation($productId, $variationId)
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(15)->get("https://maximfood.com/wp-json/wc/v3/products/{$productId}/variations/{$variationId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error("Failed to get variation", ['product_id' => $productId, 'variation_id' => $variationId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function formatVariant($attributes)
    {
        if (empty($attributes)) {
            return null;
        }

        $variants = [];
        foreach ($attributes as $attribute) {
            $name = $attribute['name'] ?? '';
            $option = $attribute['option'] ?? '';
            if ($name && $option) {
                $variants[] = "{$name}: {$option}";
            }
        }

        return !empty($variants) ? implode(', ', $variants) : null;
    }

    private function calculateDiscount($product)
    {
        $regularPrice = (float) ($product['regular_price'] ?? 0);
        $salePrice = (float) ($product['sale_price'] ?? 0);

        if ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice) {
            return (string) ($regularPrice - $salePrice);
        }

        return '0.00';
    }

    private function getDiscountType($product)
    {
        $regularPrice = (float) ($product['regular_price'] ?? 0);
        $salePrice = (float) ($product['sale_price'] ?? 0);

        if ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice) {
            return 'flat'; // يمكنك تغييرها إلى 'percent' حسب نظامك
        }

        return null;
    }

    private function isFeatured($product)
    {
        // فحص إذا كان المنتج مميز
        if (isset($product['featured']) && $product['featured']) {
            return 1;
        }

        // فحص التاجز
        if (isset($product['tags'])) {
            foreach ($product['tags'] as $tag) {
                if (strtolower($tag['name']) === 'featured') {
                    return 1;
                }
            }
        }

        return 0;
    }







    //product variants
    // public function syncAllProductVariantsInBatches(Request $request)
    // {
    //     try {
    //         $productVariantsPerPage = $request->get('per_page', 10);
    //         $delayBetweenJobs = $request->get('delay', 5);
    //         $syncDeleted = $request->get('sync_deleted', true);

    //         $totalProductVariants = $this->getTotalProductVariantsCount($request->product_id);
    //         if ($totalProductVariants === 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'لا توجد منتجات للمزامنة'
    //             ], 404);
    //         }

    //         $totalPages = ceil($totalProductVariants / $productVariantsPerPage);

    //         // إنشاء jobs
    //         for ($page = 1; $page <= $totalPages; $page++) {
    //             $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل
    //             ProcessWordPressProductVariantsBatch::dispatch( $page, $productVariantsPerPage, $isLastBatch, $request->product_id)
    //                 ->delay(now()->addSeconds($page * $delayBetweenJobs));
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
    //             'sync_details' => [
    //                 'total_categories' => $totalProductVariants,
    //                 'total_pages' => $totalPages,
    //                 'categories_per_page' => $totalProductVariants,
    //                 'sync_deleted' => $syncDeleted,
    //                 'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'فشل في بدء المزامنة',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }




    // private function getTotalProductVariantsCount($product_id)
    // {
    //     try {
    //         $response = Http::withBasicAuth(
    //             env('CONSUMER_KEY'),
    //             env('CONSUMER_SECRET')
    //         )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products/' . $product_id . '/variations', [
    //             'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
    //             'page' => 1
    //         ]);

    //         if ($response->successful()) {
    //             // الحصول على العدد الإجمالي من headers
    //             $totalCategories = $response->header('X-WP-Total');

    //             if ($totalCategories) {
    //                 return (int) $totalCategories;
    //             }

    //             // إذا لم يكن header متاحاً، استخدم طريقة بديلة
    //             return $this->calculateTotalProductVariantsAlternative($product_id);
    //         }

    //         return 0;

    //     } catch (\Exception $e) {
    //         Log::error("Failed to get total categories count", ['error' => $e->getMessage()]);
    //         return 0;
    //     }
    // }

    // private function calculateTotalProductVariantsAlternative($product_id)
    // {
    //     try {
    //         $page = 1;
    //         $totalProductVariants = 0;

    //         do {
    //             $response = Http::withBasicAuth(
    //                 env('CONSUMER_KEY'),
    //                 env('CONSUMER_SECRET')
    //             )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products/' . $product_id . '/variations', [
    //                 'per_page' => 100,
    //                 'page' => $page,
    //                 '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
    //             ]);

    //             if ($response->successful()) {
    //                 $productVariants = $response->json();
    //                 $totalProductVariants += count($productVariants);
    //                 $page++;

    //                 // تأخير قصير بين الطلبات
    //                 usleep(200000); // 0.2 ثانية
    //             } else {
    //                 break;
    //             }

    //         } while (count($productVariants) == 100);

    //         return $totalProductVariants;

    //     } catch (\Exception $e) {
    //         Log::error("Failed to calculate total categories", ['error' => $e->getMessage()]);
    //         return 0;
    //     }
    // }



    //product variants

      public function syncAllProductVariantsInBatches(Request $request)
    {
//        dd(collect($this->getAllProductIds())->count());
//        foreach ($this->getAllProductIds() as $product_id) {
//            $product=Product::where('wordpress_id',$product_id)->first();
//            dump([
//                'product_id' => @$product_id,
//                'name' => @$product->name,
//                'language' => @$product->lang
//            ]);
//        }
        try {
            $productsPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $productIds = $this->getAllProductIds();

            if (empty($productIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $productChunks = array_chunk($productIds, $productsPerPage);
            $totalChunks = count($productChunks);

            foreach ($productChunks as $index => $chunk) {
                $isLastBatch = ($index === ($totalChunks - 1)) && $syncDeleted;

                ProcessWordPressProductVariantsBatch::dispatch($chunk, $isLastBatch)
                    ->delay(now()->addSeconds($index * $delayBetweenJobs));
            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalChunks} مهمة لمزامنة المتغيرات" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_products' => count($productIds),
                    'total_batches' => $totalChunks,
                    'products_per_batch' => $productsPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalChunks * $delayBetweenJobs) . ' ثانية'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء مزامنة المتغيرات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function syncAllCitiesInBatches(Request $request)
{
    //        try {
    $citiesPerPage = $request->get('per_page', 10);
    $delayBetweenJobs = $request->get('delay', 5);
    $syncDeleted = $request->get('sync_deleted', true);

//    $totalCities = $this->getTotalProductsCount();

//    if ($totalCities === 0) {
//        return response()->json([
//            'success' => false,
//            'message' => 'لا توجد منتجات للمزامنة'
//        ], 404);
//    }

//    $totalPages = ceil($totalCities / $citiesPerPage);

    // إنشاء jobs
//    for ($page = 1; $page <= $totalPages; $page++) {
//        $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل

        ProcessWordPressCitiesBatch::dispatch(1, $citiesPerPage)
            ->delay(now()->addSeconds(1 * $delayBetweenJobs));
//    }

    return response()->json([
        'success' => true,
        'message' => "تم إنشاء  مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
        'sync_details' => [
            'total_products' => 1,
            'total_pages' => 1,
            'products_per_page' => $citiesPerPage,
            'sync_deleted' => $syncDeleted,
            'estimated_time' => (1 * $delayBetweenJobs) . ' ثانية'
        ]
    ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء المزامنة',
//                'error' => $e->getMessage()
//            ], 500);
//        }
}


    public function syncAllPartnersInBatches(Request $request)
    {
        //        try {
        $partnersPerPage = $request->get('per_page', 1);
        $delayBetweenJobs = $request->get('delay', 5);
        $syncDeleted = $request->get('sync_deleted', true);

//    $totalCities = $this->getTotalProductsCount();

//    if ($totalCities === 0) {
//        return response()->json([
//            'success' => false,
//            'message' => 'لا توجد منتجات للمزامنة'
//        ], 404);
//    }

//    $totalPages = ceil($totalCities / $citiesPerPage);

        // إنشاء jobs
//    for ($page = 1; $page <= $totalPages; $page++) {
//        $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل

        ProcessWordPressPartnersBatch::dispatch(1, $partnersPerPage)
            ->delay(now()->addSeconds(1 * $delayBetweenJobs));
//    }

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء  مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
            'sync_details' => [
                'total_products' => 1,
                'total_pages' => 1,
                'products_per_page' => $partnersPerPage,
                'sync_deleted' => $syncDeleted,
                'estimated_time' => (1 * $delayBetweenJobs) . ' ثانية'
            ]
        ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء المزامنة',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }


    public function syncAllBuyItTogetherInBatches(Request $request)
    {
//        try {
            $productsPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $productIds = $this->getAllProductFtbIds();
//            dd($productIds);
            if (empty($productIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $productChunks = array_chunk($productIds, $productsPerPage);
            $totalChunks = count($productChunks);

            foreach ($productChunks as $index => $chunk) {
                $isLastBatch = ($index === ($totalChunks - 1)) && $syncDeleted;

                ProcessWordPressProductBuyItTogetherBatch::dispatch($chunk, $isLastBatch)
                    ->delay(now()->addSeconds($index * $delayBetweenJobs));
            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalChunks} مهمة لمزامنة المتغيرات" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_products' => count($productIds),
                    'total_batches' => $totalChunks,
                    'products_per_batch' => $productsPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalChunks * $delayBetweenJobs) . ' ثانية'
                ]
            ]);

//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء مزامنة المتغيرات',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }

    public function syncAllShippingZoneLocationsInBatches(Request $request)
    {
//        try {
            $shippingZonesPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $shippingZoneId = $this->getAllShippingZoneIds();
            if (empty($shippingZoneId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $shippingZoneChunks = array_chunk($shippingZoneId, $shippingZonesPerPage);
            $totalChunks = count($shippingZoneChunks);

            foreach ($shippingZoneChunks as $index => $chunk) {
                $isLastBatch = ($index === ($totalChunks - 1)) && $syncDeleted;
                ProcessWordPressShippingZoneLocationsBatch::dispatch($chunk, $isLastBatch)
                    ->delay(now()->addSeconds($index * $delayBetweenJobs));
//                dd('lkklmklklkl');

            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalChunks} مهمة لمزامنة المتغيرات" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_products' => count($shippingZoneId),
                    'total_batches' => $totalChunks,
                    'products_per_batch' => $shippingZonesPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalChunks * $delayBetweenJobs) . ' ثانية'
                ]
            ]);

//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء مزامنة المتغيرات',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }

    public function syncAllShippingZoneMethodsInBatches(Request $request)
    {
//        try {
        $shippingZonesPerPage = $request->get('per_page', 10);
        $delayBetweenJobs = $request->get('delay', 5);
        $syncDeleted = $request->get('sync_deleted', true);

        $shippingZoneId = $this->getAllShippingZoneIds();
        if (empty($shippingZoneId)) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد طرق دفع للمزامنة'
            ], 404);
        }

        $shippingZoneChunks = array_chunk($shippingZoneId, $shippingZonesPerPage);
        $totalChunks = count($shippingZoneChunks);

        foreach ($shippingZoneChunks as $index => $chunk) {
            $isLastBatch = ($index === ($totalChunks - 1)) && $syncDeleted;
            ProcessWordPressShippingZoneMethodsBatch::dispatch($chunk, $isLastBatch)
                ->delay(now()->addSeconds($index * $delayBetweenJobs));
//                dd('lkklmklklkl');

        }

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء {$totalChunks} مهمة لمزامنة المتغيرات" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
            'sync_details' => [
                'total_products' => count($shippingZoneId),
                'total_batches' => $totalChunks,
                'products_per_batch' => $shippingZonesPerPage,
                'sync_deleted' => $syncDeleted,
                'estimated_time' => ($totalChunks * $delayBetweenJobs) . ' ثانية'
            ]
        ]);

//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء مزامنة المتغيرات',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }

//    private function getAllProductFtbIds()
//    {
////        try {
//            $allIds = [];
//            $page = 1;
//            $perPage = 100;
//
//            do {
//                $response = Http::withBasicAuth(
//                    env('CONSUMER_KEY'),
//                    env('CONSUMER_SECRET')
//                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
//                    'per_page' => $perPage,
//                    'page' => $page,
//                    '_fields' => 'meta_data'
//                ]);
//                $metaData = collect($response->json()[0]['meta_data']);
//                $woodmartFbtBundles = $metaData->firstWhere('key', 'woodmart_fbt_bundles_id');
//
//                $ids = collect($woodmartFbtBundles['value'] ?? [])
//                    ->filter()
//                    ->values()
//                    ->toArray();
//
//                dd($ids);
//                if ($response->successful()) {
//                    $products = $response->json();
//                    foreach ($products as $product) {
//                        $allIds[] = $product['id'];
//                    }
//                    $page++;
//                    usleep(200000); // 0.2 second delay
//                } else {
//                    break;
//                }
//            } while (count($products) === $perPage);
//
//            return $allIds;
////        } catch (\Exception $e) {
////            Log::error("Failed to get all product IDs", ['error' => $e->getMessage()]);
////            return [];
////        }
//    }




    private function getAllProductFtbIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
                'per_page' => $perPage,
                'page' => $page,
                '_fields' => 'id,meta_data'
            ]);

            if ($response->successful()) {
                $products = $response->json();

                foreach ($products as $product) {
                    // Add the main product ID
                    $allIds[] = $product['id'];

                    // Get and process FBT IDs from meta_data
                    $metaData = collect($product['meta_data'] ?? []);
                    $woodmartFbtBundles = $metaData->firstWhere('key', 'woodmart_fbt_bundles_id');

                    if ($woodmartFbtBundles && isset($woodmartFbtBundles['value'])) {
                        $fbtIds = collect($woodmartFbtBundles['value'] ?? [])
                            ->filter()
                            ->values()
                            ->toArray();

                        // Add FBT IDs to the allIds array
                        $allIds = array_merge($allIds, $fbtIds);
                    }
                }

                $page++;
                usleep(200000);
            } else {
                break;
            }
        } while (count($products) === $perPage);

        // Remove duplicates and reindex
        $allIds = array_values(array_unique($allIds));
        $result = collect($allIds)
            ->filter(function ($id) {
                return is_string($id) && !empty($id);
            })
            ->values()
            ->toArray();

//        dd($result);
        return $result;
    }


//    private function getAllProductIds()
//    {
//        try {
//            $allIds = [];
//            $page = 1;
//            $perPage = 100;
//
//            do {
//                $response = Http::withBasicAuth(
//                    env('CONSUMER_KEY'),
//                    env('CONSUMER_SECRET')
//                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
//                    'per_page' => $perPage,
//                    'page' => $page,
//                    '_fields' => 'id'
//                ]);
//
//                if ($response->successful()) {
//                    $products = $response->json();
//                    foreach ($products as $product) {
//                        $allIds[] = $product['id'];
//                    }
//                    $page++;
//                    usleep(200000); // 0.2 second delay
//                } else {
//                    break;
//                }
//            } while (count($products) === $perPage);
//
//            return $allIds;
//        } catch (\Exception $e) {
//            Log::error("Failed to get all product IDs", ['error' => $e->getMessage()]);
//            return [];
//        }
//    }





    private function getAllProductIds()
    {
        try {
            $allIds = [];
            $languages = ['en', 'ar'];

            foreach ($languages as $lang) {
                $page = 1;
                $perPage = 100;

                do {
                    $response = Http::withBasicAuth(
                        env('CONSUMER_KEY'),
                        env('CONSUMER_SECRET')
                    )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/products', [
                        'per_page' => $perPage,
                        'page' => $page,
                        '_fields' => 'id',
                        'lang' => $lang
                    ]);

                    if ($response->successful()) {
                        $products = $response->json();
                        foreach ($products as $product) {
                            $allIds[] = $product['id'];
                        }
                        $page++;
                        usleep(200000); // 0.2 second delay
                    } else {
                        break;
                    }
                } while (count($products) === $perPage);
            }

            // Remove duplicates in case some products exist in both languages
            $allIds = array_unique($allIds);

            return $allIds;
        } catch (\Exception $e) {
            Log::error("Failed to get all product IDs", ['error' => $e->getMessage()]);
            return [];
        }
    }


    private function getAllShippingZoneIds()
    {
        try {
            $allIds = [];
            $page = 1;
            $perPage = 100;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/shipping/zones', [
                    'per_page' => $perPage,
                    'page' => $page,
                    '_fields' => 'id'
                ]);

                if ($response->successful()) {
                    $shippingZones = $response->json();
                    foreach ($shippingZones as $shippingZone) {
                        $allIds[] = $shippingZone['id'];
                    }
                    $page++;
                    usleep(200000); // 0.2 second delay
                } else {
                    break;
                }
            } while (count($shippingZones) === $perPage);

            return $allIds;
        } catch (\Exception $e) {
            Log::error("Failed to get all shipping zones IDs", ['error' => $e->getMessage()]);
            return [];
        }
    }



    //customers

     public function syncAllCustomersInBatches(Request $request)
    {
        Log::info('Starting customer sync');
        try {
           $customersPerPage = $request->get('per_page', 10);
        $delayBetweenJobs = $request->get('delay', 5);
        $syncDeleted = $request->get('sync_deleted', true);

        $totalCustomers = $this->getTotalCustomersCount();

        Log::info('Starting customer sync', [
            'total_customers' => $totalCustomers,
            'per_page' => $customersPerPage,
            'delay' => $delayBetweenJobs,
            'sync_deleted' => $syncDeleted
        ]);
        if ($totalCustomers === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No customers found for sync'
            ], 404);
        }

        $totalPages = ceil($totalCustomers / $customersPerPage);
            Log::info('before dispatch');

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages) && $syncDeleted;

            ProcessWordPressCustomersBatch::dispatch($page, $customersPerPage, $isLastBatch)
                ->delay(now()->addSeconds($page * $delayBetweenJobs));
        }
        Log::info(message: 'after dispatch');

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_customers' => $totalCustomers,
                    'total_pages' => $totalPages,
                    'customers_per_page' => $customersPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء المزامنة',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function syncAllOrdersInBatches(Request $request)
    {

        Log::info('Starting orders sync');
//        try {
            $ordersPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $totalOrders = $this->getTotalOrdersCount();

            Log::info('Starting customer sync', [
                'total_customers' => $totalOrders,
                'per_page' => $ordersPerPage,
                'delay' => $delayBetweenJobs,
                'sync_deleted' => $syncDeleted
            ]);


            if ($totalOrders === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No customers found for sync'
                ], 404);
            }

            $totalPages = ceil($totalOrders / $ordersPerPage);
            Log::info('before dispatch');

            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted;
                ProcessWordPressOrdersBatch::dispatch($page, $ordersPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }
            Log::info(message: 'after dispatch');

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_customers' => $totalOrders,
                    'total_pages' => $totalPages,
                    'customers_per_page' => $ordersPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
                ]
            ]);
//        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'فشل في بدء المزامنة',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }

    private function getTotalOrdersCount()
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/orders', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalOrders = $response->header('X-WP-Total');

                if ($totalOrders) {
                    return (int) $totalOrders;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalOrdersAlternative();
            }

            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to get total customers count", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function calculateTotalOrdersAlternative()
    {
        try {
            $page = 1;
            $totalOrders = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/orders', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $orders = $response->json();
                    $totalOrders += count($orders);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }
            } while (count($orders) == 100);

            return $totalOrders;
        } catch (\Exception $e) {
            Log::error("Failed to calculate total customers", ['error' => $e->getMessage()]);
            return 0;
        }
    }

     public function syncAllCouponsInBatches(Request $request)
    {


        Log::info('Starting coupon sync');
        try {
            $couponsPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $totalCoupons = $this->getTotalCouponsCount();

            Log::info('Starting coupon sync', [
                'total_coupons' => $totalCoupons,
                'per_page' => $couponsPerPage,
                'delay' => $delayBetweenJobs,
                'sync_deleted' => $syncDeleted
            ]);


            if ($totalCoupons === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No coupons found for sync'
                ], 404);
            }

            $totalPages = ceil($totalCoupons / $couponsPerPage);
                Log::info('before dispatch');

            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted;

                ProcessWordPressCouponsBatch::dispatch($page, $couponsPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }
            Log::info(message: 'after dispatch');

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_coupons' => $totalCoupons,
                    'total_pages' => $totalPages,
                    'coupons_per_page' => $couponsPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء المزامنة',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function syncAllShippingZonesInBatches(Request $request)
    {


        Log::info('Starting shipping zone sync');
        try {
            $shippingZonesPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $totalShippingZones = $this->getTotalShippingZonesCount();

            Log::info('Starting shipping zone sync', [
                'total_shipping_zone' => $totalShippingZones,
                'per_page' => $shippingZonesPerPage,
                'delay' => $delayBetweenJobs,
                'sync_deleted' => $syncDeleted
            ]);


            if ($totalShippingZones === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shipping zones found for sync'
                ], 404);
            }

            $totalPages = ceil($totalShippingZones / $shippingZonesPerPage);
            Log::info('before dispatch');

            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted;

                ProcessWordPressShippingZonesBatch::dispatch($page, $shippingZonesPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }
            Log::info(message: 'after dispatch');

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_shipping_zones' => $totalShippingZones,
                    'total_pages' => $totalPages,
                    'shipping_zones_per_page' => $shippingZonesPerPage,
                    'sync_deleted' => $syncDeleted,
                    'estimated_time' => ($totalPages * $delayBetweenJobs) . ' ثانية'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في بدء المزامنة',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function getTotalShippingZonesCount()
    {
//        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/shipping/zones', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalCustomers = $response->header('X-WP-Total');

                if ($totalCustomers) {
                    return (int) $totalCustomers;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalShippingZonesAlternative();
            }

            return 0;
//        } catch (\Exception $e) {
//            Log::error("Failed to get total shipping zones count", ['error' => $e->getMessage()]);
//            return 0;
//        }
    }

    private function calculateTotalShippingZonesAlternative()
    {
        try {
            $page = 1;
            $totalShippingZones = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/shipping/zones', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $shippingZones = $response->json();
                    $totalShippingZones += count($shippingZones);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }
            } while (count($shippingZones) == 100);

            return $totalShippingZones;
        } catch (\Exception $e) {
            Log::error("Failed to calculate total shipping zones", ['error' => $e->getMessage()]);
            return 0;
        }
    }




    private function getTotalCustomersCount()
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/customers', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalCustomers = $response->header('X-WP-Total');

                if ($totalCustomers) {
                    return (int) $totalCustomers;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalCustomersAlternative();
            }

            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to get total customers count", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function calculateTotalCustomersAlternative()
    {
        try {
            $page = 1;
            $totalCustomers = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/customers', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $customers = $response->json();
                    $totalCustomers += count($customers);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }
            } while (count($customers) == 100);

            return $totalCustomers;
        } catch (\Exception $e) {
            Log::error("Failed to calculate total customers", ['error' => $e->getMessage()]);
            return 0;
        }
    }
    private function getTotalCouponsCount()
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/coupons', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalCustomers = $response->header('X-WP-Total');

                if ($totalCustomers) {
                    return (int) $totalCustomers;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalCouponsAlternative();
            }

            return 0;
        } catch (\Exception $e) {
            Log::error("Failed to get total coupons count", ['error' => $e->getMessage()]);
            return 0;
        }
    }


     private function calculateTotalCouponsAlternative()
    {
        try {
            $page = 1;
            $totalCoupons = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)->get('https://maximfood.com/wp-json/wc/v3/coupons', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $coupons = $response->json();
                    $totalCoupons += count($coupons);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }
            } while (count($coupons) == 100);

            return $totalCoupons;
        } catch (\Exception $e) {
            Log::error("Failed to calculate total coupons", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    public function syncAllBannerInBatches(Request $request)
    {
        $partnersPerPage = $request->get('per_page', 1);
        $delayBetweenJobs = $request->get('delay', 5);
        $syncDeleted = $request->get('sync_deleted', true);

        ProcessWordpressBannerBatch::dispatch(1, $partnersPerPage)
            ->delay(now()->addSeconds(1 * $delayBetweenJobs));

        return response()->json([
            'success' => true,
            'message' => "تم إنشاء  مهمة للمزامنة البانرز",
            'sync_details' => [
                'estimated_time' => (1 * $delayBetweenJobs) . ' ثانية'
            ]
        ]);
    }


}
