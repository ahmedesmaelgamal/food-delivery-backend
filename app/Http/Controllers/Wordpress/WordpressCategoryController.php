<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Jobs\ProcessWordPressProductsBatch;
use App\Jobs\ProcessWordPressCategoriesBatch;
use App\Jobs\ProcessWordpressCategoriesInArabicBatch;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class WordpressCategoryController extends Controller
{



    public function syncAllCategoriesInBatches(Request $request)
    {
        try {
            $categoriesPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);
//            dd($categoriesPerPage);

            $totalCategories = $this->getTotalCategoriesCount();
            if ($totalCategories === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $totalPages = ceil($totalCategories / $categoriesPerPage);

            // إنشاء jobs
            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل
                ProcessWordPressCategoriesBatch::dispatch($page, $categoriesPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_categories' => $totalCategories,
                    'total_pages' => $totalPages,
                    'categories_per_page' => $categoriesPerPage,
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

    public function syncAllCategoriesInArabicInBatches(Request $request)
    {
        try {
            $categoriesPerPage = $request->get('per_page', 10);
            $delayBetweenJobs = $request->get('delay', 5);
            $syncDeleted = $request->get('sync_deleted', true);

            $totalCategories = $this->getTotalCategoriesCount();
            if ($totalCategories === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد منتجات للمزامنة'
                ], 404);
            }

            $totalPages = ceil($totalCategories / $categoriesPerPage);

            // إنشاء jobs
            for ($page = 1; $page <= $totalPages; $page++) {
                $isLastBatch = ($page === $totalPages) && $syncDeleted; // الدفعة الأخيرة فقط إذا كان الحذف مفعل
                ProcessWordpressCategoriesInArabicBatch::dispatch($page, $categoriesPerPage, $isLastBatch)
                    ->delay(now()->addSeconds($page * $delayBetweenJobs));
            }

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء {$totalPages} مهمة للمزامنة" . ($syncDeleted ? " مع مزامنة الحذف في النهاية" : ""),
                'sync_details' => [
                    'total_categories' => $totalCategories,
                    'total_pages' => $totalPages,
                    'categories_per_page' => $categoriesPerPage,
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




    private function getTotalCategoriesCount()
    {
        try {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(30)
                ->withoutVerifying()

                ->get('https://maximfood.com/wp-json/wc/v3/products/categories', [
                'per_page' => 1, // نحتاج منتج واحد فقط للحصول على العدد الإجمالي
                'page' => 1
            ]);
//            dd($response);

            if ($response->successful()) {
                // الحصول على العدد الإجمالي من headers
                $totalCategories = $response->header('X-WP-Total');

                if ($totalCategories) {
                    return (int) $totalCategories;
                }

                // إذا لم يكن header متاحاً، استخدم طريقة بديلة
                return $this->calculateTotalCategoriesAlternative();
            }

            return 0;

        } catch (\Exception $e) {
            Log::error("Failed to get total categories count", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    private function calculateTotalCategoriesAlternative()
    {
        try {
            $page = 1;
            $totalCategories = 0;

            do {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(30)
                    ->withoutVerifying()

                    ->get('https://maximfood.com/wp-json/wc/v3/products/categories', [
                    'per_page' => 100,
                    'page' => $page,
                    '_fields' => 'id' // نحتاج فقط ID لتوفير البيانات
                ]);

                if ($response->successful()) {
                    $categories = $response->json();
                    $totalCategories += count($categories);
                    $page++;

                    // تأخير قصير بين الطلبات
                    usleep(200000); // 0.2 ثانية
                } else {
                    break;
                }

            } while (count($categories) == 100);

            return $totalCategories;

        } catch (\Exception $e) {
            Log::error("Failed to calculate total categories", ['error' => $e->getMessage()]);
            return 0;
        }
    }


    // private function syncCategoryStocks($categoryRecord, $wpCategory)
    // {
    //     // حذف المخزون القديم
    //     // ProductStock::where('product_id', $productRecord->id)->delete();

    //     // إضافة المخزون الجديد
    //     if (!empty($wpProduct['variations'])) {
    //         // منتج له variations
    //         foreach ($wpProduct['variations'] as $variationId) {
    //             $variation = $this->getProductVariation($wpProduct['id'], $variationId);
    //             if ($variation) {
    //                 ProductStock::create([
    //                     'product_id' => $productRecord->id,
    //                     'variant' => $this->formatVariant($variation['attributes'] ?? []),
    //                     'sku' => $variation['sku'] ?? '',
    //                     'price' => (float) ($variation['price'] ?? 0),
    //                     'qty' => (int) ($variation['stock_quantity'] ?? 0),
    //                 ]);
    //             }
    //         }
    //     } else {
    //         // منتج بسيط بدون variations
    //         // ProductStock::create([
    //         //     'product_id' => $productRecord->id,
    //         //     'variant' => null,
    //         //     'sku' => $wpProduct['sku'] ?? '',
    //         //     'price' => (float) ($wpProduct['price'] ?? 0),
    //         //     'qty' => (int) ($wpProduct['stock_quantity'] ?? 0),
    //         // ]);
    //     }
    // }

    // private function getProductVariation($productId, $variationId)
    // {
    //     try {
    //         $response = Http::withBasicAuth(
    //             env('CONSUMER_KEY'),
    //             env('CONSUMER_SECRET')
    //         )->timeout(15)->get("https://maximfood.com/wp-json/wc/v3/products/{$productId}/variations/{$variationId}");

    //         return $response->successful() ? $response->json() : null;
    //     } catch (\Exception $e) {
    //         Log::error("Failed to get variation", ['product_id' => $productId, 'variation_id' => $variationId, 'error' => $e->getMessage()]);
    //         return null;
    //     }
    // }

    // private function formatVariant($attributes)
    // {
    //     if (empty($attributes)) {
    //         return null;
    //     }

    //     $variants = [];
    //     foreach ($attributes as $attribute) {
    //         $name = $attribute['name'] ?? '';
    //         $option = $attribute['option'] ?? '';
    //         if ($name && $option) {
    //             $variants[] = "{$name}: {$option}";
    //         }
    //     }

    //     return !empty($variants) ? implode(', ', $variants) : null;
    // }

    // private function calculateDiscount($product)
    // {
    //     $regularPrice = (float) ($product['regular_price'] ?? 0);
    //     $salePrice = (float) ($product['sale_price'] ?? 0);

    //     if ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice) {
    //         return (string) ($regularPrice - $salePrice);
    //     }

    //     return '0.00';
    // }

    // private function getDiscountType($product)
    // {
    //     $regularPrice = (float) ($product['regular_price'] ?? 0);
    //     $salePrice = (float) ($product['sale_price'] ?? 0);

    //     if ($regularPrice > 0 && $salePrice > 0 && $salePrice < $regularPrice) {
    //         return 'flat'; // يمكنك تغييرها إلى 'percent' حسب نظامك
    //     }

    //     return null;
    // }

    // private function isFeatured($product)
    // {
    //     // فحص إذا كان المنتج مميز
    //     if (isset($product['featured']) && $product['featured']) {
    //         return 1;
    //     }

    //     // فحص التاجز
    //     if (isset($product['tags'])) {
    //         foreach ($product['tags'] as $tag) {
    //             if (strtolower($tag['name']) === 'featured') {
    //                 return 1;
    //             }
    //         }
    //     }

    //     return 0;
    // }



}
