<?php

namespace App\Http\Controllers\RestAPI\v5\product;

use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\CategoryResource;
use App\Http\Resources\RestAPI\v5\CityResource;
use App\Http\Resources\RestAPI\v5\DigitalProductVariationResource;
use App\Http\Resources\RestAPI\v5\productArrivalResource;
use App\Http\Resources\RestAPI\v5\ShippingZoneLocationResource;
use App\Http\Resources\RestAPI\v5\ShippingZoneMethodResource;
use App\Http\Resources\RestAPI\v5\ShippingZoneResource;
use App\Http\Resources\RestAPI\v5\StateResource;
use App\Models\BuyItTogether;
use App\Models\City;
use App\Models\DigitalProductVariation;
use App\Models\ShippingZone;
use App\Models\ShippingZoneLocation;
use App\Models\ShippingZoneMethod;
use App\Models\State;
use App\User;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\RestAPI\v5\ProductResource;
use App\Models\Author;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DigitalProductAuthor;
use App\Models\DigitalProductPublishingHouse;
use App\Models\MostDemanded;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\PublishingHouse;
use App\Models\RecentViewdProduct;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\Shop;
use App\Models\StockClearanceProduct;
use App\Models\Wishlist;
use App\Services\ProductService;
use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use FileManagerTrait, CacheManagerTrait;

    public function __construct(
        private Product                                            $product,
        private Order                                              $order,
        private MostDemanded                                       $most_demanded,
        private readonly AuthorRepositoryInterface                 $authorRepo,
        private readonly PublishingHouseRepositoryInterface        $publishingHouseRepo,
        private readonly ProductService                            $productService,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly CategoryRepositoryInterface               $categoryRepo,
    ) {}

    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'success_fetching_data',
            'status' => 200
        ]);
    }
    public function errorResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'error fetching data',
            'status' => 500
        ]);
    }

    public function getCities()
    {
        $state=State::with('cities')->get();
        $data=StateResource::collection($state);
        return $this->responseMsg(
            'success_fetching_data',
            $data,
            200
        );
    }
    public function responseMsg($msg, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }

//    public function get_products(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        }
//        $categoryWordpressId = null;
//        if ($request->has('category_id')) {
//            $categoryWordpressId = @Category::where('wordpress_id', $request->category_id)->where('lang',$lang)->first()->wordpress_id;
//        }
//        $products_per_page = $request->input('products_per_page', null);
//        $page_number = $request->input('page_number', 1);
//        //    $recommendedProducts = ProductManager::getBestSellingProductsList($request);
//
//        $query = Product::where("status", 1)->where('lang',$lang)->where('slug', '!=', null);
//        // Apply all your filters
//        $query->when($request->filled('name'), function ($q) use ($request) {
//            $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->name) . '%']);
//        });
//
//        $query->when($request->filled('start_price'), function ($q) use ($request) {
//            $q->where('unit_price', '>=', $request->start_price);
//        });
//
//        $query->when($request->filled('end_price'), function ($q) use ($request) {
//            $q->where('unit_price', '<=', $request->end_price);
//        });
//
//        if ($categoryWordpressId) {
//            $query->when($request->filled('category_id'), function ($q) use ($categoryWordpressId) {
//                $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(category_ids, '[', ''), ']', ''))",
//                    [$categoryWordpressId]);
//            });
//        }
//        $query->when($request->filled('brand_id'), function ($q) use ($request) {
//            $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(brands, '[', ''), ']', ''))",
//                [$request->brand_id]);
//        });
//        if ($request->filled('type')) {
//            if ($request->type == 'new_arrival') {
//                $query->orderBy('wordpress_id', 'desc');
//            } elseif ($request->type == 'offer') {
//                $query->whereOnSale('1')->where('sale_price', '!=', '0')->Where('on_sale', 1);
//            } elseif ($request->type == 'recommended') {
//                $recommendedProductsIds = Product::inRandomOrder()
//                    ->limit(10)
//                    ->where('slug', '!=', null)
//                    ->where('lang', $lang)
//                    ->pluck('id')
//                    ->toArray();
//
//                $query->whereIn('id', $recommendedProductsIds);
//
//            } elseif ($request->type == 'best_seller') {
//
////                $allProductsWithSales = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
////                    ->select(
////                        'products.id',
////                        'products.wordpress_id',
////                        'products.name',
////                        'products.thumbnail',
////                        'products.current_stock',
////                        'products.status',
////                        DB::raw('COALESCE(SUM(order_details.qty), 0) as total_sold'),
////                        DB::raw('COALESCE(COUNT(order_details.id), 0) as order_count')
////                    )
////                    ->groupBy(
////                        'products.id',
////                        'products.wordpress_id',
////                        'products.name',
////                        'products.thumbnail',
////                        'products.current_stock',
////                        'products.status'
////                    )
////                    ->where('products.lang', $lang)
////                    ->orderByDesc('total_sold')
////                    ->get();
////
////                $bestSellingProductIds = Product::whereIn('wordpress_id', $allProductsWithSales->pluck('wordpress_id'))
////                    ->where('current_stock', '>=', 1)
////                    ->where('slug', '!=', null)
////                    ->where('lang', $lang)
////                    ->pluck('wordpress_id')
////                    ->toArray();
//
////                if (!empty($bestSellingProductIds)) {
////                    $query->whereIn('wordpress_id', $bestSellingProductIds)
////                        ->orderByRaw('FIELD(wordpress_id, ' . implode(',', $bestSellingProductIds) . ')');
////                } else {
////                    $query->where('id', 0); // Return empty if no best sellers
////                }
//
//
//
//
//                try {
////            $bestSellingProducts = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
////                ->select(
////                    'products.*',
////                    DB::raw('COALESCE(SUM(order_details.qty), 0) as total_sold')
////                )
////                ->where('products.status', 1)
////                ->where('products.current_stock', '>=', 1)
////                ->where('lang',$lang)
////                ->groupBy('products.id')
////                ->orderBy('total_sold', 'desc')
////                ->limit(10)
////                ->get();
//
//                    $query->where('lang',$lang)->where('status',1)->where('current_stock','>=',1)->orderBy('total_sales','desc')->limit(10)->get();
//////            dd($bestSellingProducts);
////                    $products = ProductArrivalResource::collection($bestSellingProducts);
//                } catch (\Exception $e) {
//                    $data['best_sellers']=[];
//                }
//
//
//
//            } elseif ($request->type == 'trending' && $request->has('category_id')) {
////                $category = Category::where('name', $request->type)->where('current_stock', '>=', 1)->where('lang',$lang)->first();
////                if ($category) {
////                    $query->where('category_id', $category->wordpress_id);
////                }
//            }
//            elseif ($request->type == 'recently_viewed') {
//                $recentlyViewed = auth()->check()
//                    ? RecentViewdProduct::where("customer_id", auth()->user()->id)
//                        ->pluck('product_id')
//                        ->toArray()
//                    : [];
//                $query->whereIn('id', $recentlyViewed);
//            }
//            elseif ($request->type == 'ready_to_fry') {
////                dd(Product::select('id', 'category_ids')->take(5)->get()->toArray());
////
////                $category = Category::where('slug', 'ready-to-fre-breaded')->where('lang',$lang)->first();
//////                dd($category->wordpress_id);
////                if ($category) {
////                    $query->where(function ($q) use ($category) {
////                        $id = $category->wordpress_id;
////                        $q->where('category_id', 'like', "%\"$id\"%")
////                            ->orWhere('category_id', 'like', "%,$id,%")
////                            ->orWhere('category_id', 'like', "$id,%")
////                            ->orWhere('category_id', 'like', "%,$id")
////                            ->orWhere('category_id', '=', $id);
////                    });
////                }
////                dd($query->get());
//
//
//
//                $category = Category::where('slug', 'ready-to-fre-breaded')
//                    ->where('lang', $lang)
//                    ->first();
//
//                if ($category) {
//                    $query->whereJsonContains('category_ids', (int) $category->wordpress_id);
//                }
//
//
//            }
//        }
//
//        // Sorting
//        if ($request->filled("sort_by")) {
//            switch ($request->sort_by) {
//                case 'lowest-price':
//                    $query->orderBy('unit_price', 'asc');
//                    break;
//                case 'highest-price':
//                    $query->orderBy('unit_price', 'desc');
//                    break;
//                case 'new-in':
//                    $query->orderBy('created_at', 'desc'); // Changed to desc for newest first
//                    break;
//                case 'best-selling':
//                    $query->withCount('orderDetails')
//                        ->orderBy('order_details_count', 'desc');
//                    break;
//            }
//        }
//
//        // Wholesale filter
//        $query->when($request->has('is_wholesale'), function ($q) use ($request) {
//            $q->where('product_type', $request->is_wholesale == 1 ? 'whole_sale' : '!=', 'whole_sale');
//        });
//
//        // Handle pagination or non-paginated response
//        if ($products_per_page == null) {
//            $products = $query->get();
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => null,
//                'products_per_page' => null,
//                'products' => productArrivalResource::collection($products),
//            ], 200);
//        } else {
//            $products = $query->paginate($products_per_page, ['*'], 'page', $page_number);
//
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => [
//                    'total' => $products->total(),
//                    'per_page' => $products->perPage(),
//                    'current_page' => $products->currentPage(),
//                    'last_page' => $products->lastPage(),
//                    'next_page_url' => $products->nextPageUrl(),
//                    'prev_page_url' => $products->previousPageUrl(),
//                    'from' => $products->firstItem(),
//                    'to' => $products->lastItem(),
//                ],
//                'products_per_page' => $products_per_page,
//                'products' => productArrivalResource::collection($products),
//            ], 200);
//        }
//    }



//    public function get_products(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        } else {
//            $lang = 'en';
//        }
//
//        $categoryWordpressId = null;
//        if ($request->has('category_id')) {
//            $categoryWordpressId = @Category::where('wordpress_id', $request->category_id)->where('lang',$lang)->first()->wordpress_id;
//        }
//        $products_per_page = $request->input('products_per_page', null);
//        $page_number = $request->input('page_number', 1);
//
//        // Remove lang filter from main query to get all products
//        $query = Product::where("status", 1)->where('slug', '!=', null);
//
//        // Apply all your filters
//        $query->when($request->filled('name'), function ($q) use ($request) {
//            $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->name) . '%']);
//        });
//
//        $query->when($request->filled('start_price'), function ($q) use ($request) {
//            $q->where('unit_price', '>=', $request->start_price);
//        });
//
//        $query->when($request->filled('end_price'), function ($q) use ($request) {
//            $q->where('unit_price', '<=', $request->end_price);
//        });
//
//        if ($categoryWordpressId) {
//            $query->when($request->filled('category_id'), function ($q) use ($categoryWordpressId) {
//                $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(category_ids, '[', ''), ']', ''))",
//                    [$categoryWordpressId]);
//            });
//        }
//
//        $query->when($request->filled('brand_id'), function ($q) use ($request) {
//            $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(brands, '[', ''), ']', ''))",
//                [$request->brand_id]);
//        });
//
//        if ($request->filled('type')) {
//            if ($request->type == 'new_arrival') {
//                $query->orderBy('wordpress_id', 'desc');
//            } elseif ($request->type == 'offer') {
//                $query->whereOnSale('1')->where('sale_price', '!=', '0')->Where('on_sale', 1);
//            } elseif ($request->type == 'recommended') {
//                $recommendedProductsIds = Product::inRandomOrder()
//                    ->limit(10)
//                    ->where('slug', '!=', null)
//                    ->pluck('id')
//                    ->toArray();
//
//                $query->whereIn('id', $recommendedProductsIds);
//
//            } elseif ($request->type == 'best_seller') {
//                $query->where('status',1)
//                    ->where('current_stock','>=',1)
//                    ->orderBy('total_sales','desc')
//                    ->limit(10);
//            } elseif ($request->type == 'recently_viewed') {
//                $recentlyViewed = auth()->check()
//                    ? RecentViewdProduct::where("customer_id", auth()->user()->id)
//                        ->pluck('product_id')
//                        ->toArray()
//                    : [];
//                $query->whereIn('id', $recentlyViewed);
//            } elseif ($request->type == 'ready_to_fry') {
//                $category = Category::where('slug', 'ready-to-fre-breaded')
//                    ->where('lang', $lang)
//                    ->first();
//
//                if ($category) {
//                    $query->whereJsonContains('category_ids', (int) $category->wordpress_id);
//                }
//            }
//        }
//
//        // Sorting
//        if ($request->filled("sort_by")) {
//            switch ($request->sort_by) {
//                case 'lowest-price':
//                    $query->orderBy('unit_price', 'asc');
//                    break;
//                case 'highest-price':
//                    $query->orderBy('unit_price', 'desc');
//                    break;
//                case 'new-in':
//                    $query->orderBy('created_at', 'desc');
//                    break;
//                case 'best-selling':
//                    $query->withCount('orderDetails')
//                        ->orderBy('order_details_count', 'desc');
//                    break;
//            }
//        }
//
//        // Wholesale filter
//        $query->when($request->has('is_wholesale'), function ($q) use ($request) {
//            $q->where('product_type', $request->is_wholesale == 1 ? 'whole_sale' : '!=', 'whole_sale');
//        });
//        // Handle pagination or non-paginated response
//        if ($products_per_page == null) {
//            $products = $query->get();
//
//            // Translate products
//            $translatedProducts = $this->translateProductsCollection($products, $lang);
//
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => null,
//                'products_per_page' => null,
//                'products' => $translatedProducts->map(function ($product) use ($lang) {
//                    return productArrivalResource::make($product)->additional(['lang' => $lang]);
//                }),
//            ], 200);
//        }else {
//
//            $products = $query->paginate($products_per_page, ['*'], 'page', $page_number);
//
//            // Translate products in paginated collection
//            $translatedProducts = $this->translateProductsCollection($products->items(), $lang);
////            dd($lang,$translatedProducts->take(10));
//            // Rebuild pagination with translated products
//            $paginatedTranslated = new \Illuminate\Pagination\LengthAwarePaginator(
//                $translatedProducts,
//                $products->total(),
//                $products->perPage(),
//                $products->currentPage(),
//                ['path' => $products->path()]
//            );
//
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => [
//                    'total' => $paginatedTranslated->total(),
//                    'per_page' => $paginatedTranslated->perPage(),
//                    'current_page' => $paginatedTranslated->currentPage(),
//                    'last_page' => $paginatedTranslated->lastPage(),
//                    'next_page_url' => $paginatedTranslated->nextPageUrl(),
//                    'prev_page_url' => $paginatedTranslated->previousPageUrl(),
//                    'from' => $paginatedTranslated->firstItem(),
//                    'to' => $paginatedTranslated->lastItem(),
//                ],
//                'products_per_page' => $products_per_page,
//                'products' => $translatedProducts->map(function ($product) use ($lang) {
//                    return productArrivalResource::make($product)->additional(['lang' => $lang]);
//                }),
//            ], 200);
//        }
//    }





    public function get_products(Request $request)
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        } else {
            $lang = 'en';
        }

        $categoryWordpressId = null;
        if ($request->has('category_id')) {
            $categoryWordpressId = @Category::where('wordpress_id', $request->category_id)->where('lang',$lang)->first()->wordpress_id;
        }
        $products_per_page = $request->input('products_per_page', null);
        $page_number = $request->input('page_number', 1);

        // ✅ CRITICAL FIX: Start with products in requested language only
        $query = Product::where("status", 1)
            ->where('slug', '!=', null)
            ->where('lang', $lang); // 👈 Only products in requested language

        // Apply all your filters
        $query->when($request->filled('name'), function ($q) use ($request) {
            $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->name) . '%']);
        });

        $query->when($request->filled('start_price'), function ($q) use ($request) {
            $q->where('unit_price', '>=', $request->start_price);
        });

        $query->when($request->filled('end_price'), function ($q) use ($request) {
            $q->where('unit_price', '<=', $request->end_price);
        });

        if ($categoryWordpressId) {
            $query->when($request->filled('category_id'), function ($q) use ($categoryWordpressId) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(category_ids, '[', ''), ']', ''))",
                    [$categoryWordpressId]);
            });
        }

        $query->when($request->filled('brand_id'), function ($q) use ($request) {
            $q->whereRaw("FIND_IN_SET(?, REPLACE(REPLACE(brands, '[', ''), ']', ''))",
                [$request->brand_id]);
        });

        if ($request->filled('type')) {
            if ($request->type == 'new_arrival') {
                $query->orderBy('wordpress_id', 'desc');
            } elseif ($request->type == 'offer') {
                $query->whereOnSale('1')->where('sale_price', '!=', '0')->Where('on_sale', 1);
            } elseif ($request->type == 'recommended') {
                $recommendedProductsIds = Product::where('lang', $lang) // 👈 Filter by language
                ->inRandomOrder()
                    ->limit(10)
                    ->where('slug', '!=', null)
                    ->pluck('id')
                    ->toArray();

                $query->whereIn('id', $recommendedProductsIds);

            } elseif ($request->type == 'best_seller') {
                $query->where('current_stock','>=',1)
                    ->orderBy('total_sales','desc')
                    ->limit(10);
            } elseif ($request->type == 'recently_viewed') {
                $recentlyViewed = auth()->check()
                    ? RecentViewdProduct::where("customer_id", auth()->user()->id)
                        ->pluck('product_id')
                        ->toArray()
                    : [];
                $query->whereIn('id', $recentlyViewed);
            } elseif ($request->type == 'ready_to_fry') {
                $category = Category::where('slug', 'ready-to-fre-breaded')
                    ->where('lang', $lang)
                    ->first();

                if ($category) {
                    $query->whereJsonContains('category_ids', (int) $category->wordpress_id);
                }
            }
        }

        // Sorting
        if ($request->filled("sort_by")) {
            switch ($request->sort_by) {
                case 'lowest-price':
                    $query->orderBy('unit_price', 'asc');
                    break;
                case 'highest-price':
                    $query->orderBy('unit_price', 'desc');
                    break;
                case 'new-in':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'best-selling':
                    $query->withCount('orderDetails')
                        ->orderBy('order_details_count', 'desc');
                    break;
            }
        }

        // Wholesale filter
        $query->when($request->has('is_wholesale'), function ($q) use ($request) {
            $q->where('product_type', $request->is_wholesale == 1 ? 'whole_sale' : '!=', 'whole_sale');
        });

        // Handle pagination or non-paginated response
        if ($products_per_page == null) {
            $products = $query->get();

            // ✅ NO NEED for translation - products are already in correct language
            return $this->responseMsg('data has been returned successfully', [
                'pagination' => null,
                'products_per_page' => null,
                'products' => $products->map(function ($product) use ($lang) {
                    return productArrivalResource::make($product)->additional(['lang' => $lang]);
                }),
            ], 200);
        } else {
            $products = $query->paginate($products_per_page, ['*'], 'page', $page_number);

            // ✅ NO NEED for translation - products are already in correct language
            return $this->responseMsg('data has been returned successfully', [
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'products_per_page' => $products_per_page,
                'products' => $products->map(function ($product) use ($lang) {
                    return productArrivalResource::make($product)->additional(['lang' => $lang]);
                }),
            ], 200);
        }
    }




















    /**
     * Translate products collection to requested language
     */
//    private function translateProductsCollection($products, $lang)
//    {
//        return collect($products)->map(function ($product) use ($lang) {
//            // If product is already in requested language, return it
//            if ($product->lang == $lang) {
//                return $product;
//            }
//
//            // Try to get translation
//            $translationField = "translation_{$lang}";
//            $translationId = $product->$translationField ?? null;
//
//            // If no translation ID set, try to find by matching criteria
//            if (!$translationId) {
//                // Try to find translation by wordpress_id relationship or other criteria
//                // This is a fallback - ideally translation fields should be populated
//                $translatedProduct = Product::where('lang', $lang)
//                    ->where('status', 1)
//                    ->whereNotNull('slug')
//                    // You might want to add more matching criteria here
//                    // ->where('some_reference_field', $product->some_reference_field)
//                    ->first();
//
//                if ($translatedProduct) {
//                    return $translatedProduct;
//                }
//
//                // If no translation found, return original
//                return $product;
//            }
//
//            // Try to find translated product
//            $translatedProduct = Product::where('wordpress_id', $translationId)
//                ->where('status', 1)
//                ->whereNotNull('slug')
//                ->first();
//
//            // Return translated if found, otherwise original
//            return $translatedProduct ?: $product;
//        })->values();
//    }







    public function coupon_check(Request $request)
    {
        $couponCode = $request->input('coupon_code');
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();
            if ($coupon && $coupon->status == 1 && $coupon->expire_date >= now() &&  $coupon->users_limit?($coupon->usage_count < $coupon->users_limit * $coupon->limit):true) {
//                    $prices_after_coupon=[];
//dd($request->header('token'));
//
//                    $user = auth()->user();
//                    dd($user);


//                    $token = $request->header('Authorization');
//                    $user=null;
//                    if ($token) {
//                        $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token)?->tokenable;
//                        dd($user);
//                    }



//                    $user=auth()->user();
//                    if (!$user) {
//                        return $this->responseMsg('unauthorized user', null, 403);
//                    }



//                    dd($user);

//                    $usedBy = json_decode($coupon->used_by, true); // decode to PHP array
//
//                    $targets = [$user['email'], $user['wordpress_id']];
//                    $filtered = collect($usedBy)->filter(function ($value) use ($targets) {
//                        return in_array($value, $targets);
//                    });
//
//                    $count = $filtered->count();
//
//                    if ($count>=$coupon->usage_limit_per_user ) {
//                        return $this->responseMsg('the user has reached the limit of coupon usage', null, 403);
//                    }
                    return $this->responseMsg('Coupon returned successfully.',
                    [
                        "discount" => (int)$coupon->discount,
                        "discount_type" => $coupon->discount_type,
                        'excluded_product_ids'=>@json_decode($coupon->excluded_product_ids),
                        'excluded_product_categories'=>@json_decode($coupon->excluded_product_categories),
                        'limit_usage_to_x_items'=>(integer)$coupon->limit_usage_to_x_items,
                        'usage_limit_per_user'=>(integer)$coupon->usage_limit_per_user,
                        'product_ids'=>json_decode($coupon->product_ids),
                        'product_categories'=>json_decode($coupon->product_categories),
                        'minimum_amount'=>(integer)$coupon->minimum_amount,
                        'maximum_amount'=>(integer)$coupon->maximum_amount?:null,
                        'product_brands'=>json_decode($coupon->product_brands),
                        'exclude_product_brands'=>json_decode($coupon->exclude_product_brands),
                        'exclude_sale_items'=>json_decode($coupon->exclude_sale_items),
//                        'prices_after_coupon'=>$prices_after_coupon,
                    ]
                    , 200);
            } else {
                return $this->responseMsg('Invalid or expired coupon code.', null, 403);
            }
        }
    }

    public function get_buy_it_together_products($id)
    {
        $buyItTogetherProducts = BuyItTogether::where('wordpress_id',$id)->first();
        return $this->responseMsg('data has been returned successfully', new \App\Http\Resources\RestAPI\v5\BuyItTogetherResource($buyItTogetherProducts), 200);
    }

//    public function products_variety(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//            // $data['banner'] = BannerResource::collection(\App\Models\Banner::where('lang', $lang)->get());
//        }
//        $viewedProducts=[];
//        if($request->has('viewed_product_ids')){
//            foreach ($request->viewed_product_ids as $key => $viewedProductId) {
//                $viewedProductIds[]=$viewedProductId;
//            }
//            $viewedProducts=productArrivalResource::collection(Product::whereIn('wordpress_id', $viewedProductIds)->where('lang',$lang)->where('slug', '!=', null)->get());
//
//        }
//
////        dd($viewedProducts,$viewedProductIds);
//        $data['recent_viewed']=$viewedProducts;
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        }
//        $request = [];
////        $orderDetails = OrderDetail::select('product_id', DB::raw('COUNT(product_id) as count'))
////            ->groupBy('product_id')
////            ->orderBy('count', 'desc')
////            ->limit(10)
////            ->get();
//
////        $bestSellerProductIds = $orderDetails->pluck('product_id')->toArray();
////        $data["best_seller"] = ProductArrivalResource::collection(Product::whereIn('id', $bestSellerProductIds)->where('current_stock', '>=', 1)->where('slug', '!=', null)->get());
//
//
//        //removed remprorly
//
//        try {
////            $bestSellingProducts = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
////                ->select(
////                    'products.*',
////                    DB::raw('COALESCE(SUM(order_details.qty), 0) as total_sold')
////                )
////                ->where('products.status', 1)
////                ->where('products.current_stock', '>=', 1)
////                ->where('products.lang',$lang)
////                ->groupBy('products.id')
////                ->orderBy('total_sold', 'desc')
////                ->limit(10)
////                ->get();
//            $bestSellingProducts=Product::where('lang',$lang)->where('status',1)->orderBy('total_sales','desc')->limit(10)->get();
//
//            $data['best_seller'] = ProductArrivalResource::collection($bestSellingProducts);
//        } catch (\Exception $e) {
//            $data['best_seller']=[];
//        }
////        $data['best_sellers']=[];
//
//
//        //removed remprorly
//        try {
////            $bestSellingProducts = Product::leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
////                ->select(
////                    'products.*',
////                    DB::raw('COALESCE(SUM(order_details.qty), 0) as total_sold')
////                )
////                ->where('products.status', 1)
////                ->where('products.current_stock', '>=', 1)
////                ->groupBy('products.id')
////                ->orderBy('total_sold', 'desc')
////                ->limit(10)
////                ->get();
//            $data['recommended'] = ProductArrivalResource::collection($bestSellingProducts);
//        } catch (\Exception $e) {
//            $data['recommended']=[];
//        }
//
//
//        $recentViewedProductIds = auth()->check() ? RecentViewdProduct::where("customer_id", auth()->user()->id)->pluck('product_id')->toArray() : [];
////        $data["recent_viewed"] = auth()->check() ? ProductArrivalResource::collection(Product::whereIn('id', $recentViewedProductIds)->where('current_stock', '>=', 1)->where('slug', '!=', null)->get()) : null;
//
////        $data["recommended"] = ProductArrivalResource::collection(Product::where('current_stock', '>=', 1)->where('slug', '!=', null)->take(10)->get());
//
//        return $this->responseMsg(
//            'data has been returned successfully',
//            $data,
//            200
//        );
//    }


//    public function products_variety(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        } else {
//            $lang = 'en';
//        }
//
//        $viewedProducts = [];
//
//        if ($request->has('viewed_product_ids')) {
//            $viewedProductIds = [];
//            foreach ($request->viewed_product_ids as $key => $viewedProductId) {
//                $viewedProductIds[] = $viewedProductId;
//            }
//
//            $products = Product::whereIn('wordpress_id', $viewedProductIds)
//                ->where('slug', '!=', null)
//                ->get();
//
//            // Translate viewed products
//            $translatedViewedProducts = $products->map(function ($product) use ($lang) {
//                if ($product->lang != $lang) {
//                    $translationField = "translation_{$lang}";
//                    $translationId = $product->$translationField ?? null;
//
//                    if ($translationId) {
//                        $translatedProduct = Product::where('wordpress_id', $translationId)
//                            ->where('status', 1)
//                            ->whereNotNull('slug')
//                            ->first();
//
//                        if ($translatedProduct) {
//                            return $translatedProduct;
//                        }
//                    }
//                }
//                return $product;
//            })->filter()->values();
//
//            $viewedProducts = $translatedViewedProducts->map(function ($product) use ($lang) {
//                return productArrivalResource::make($product)->additional(['lang' => $lang]);
//            });
//        }
//
//        $data['recent_viewed'] = $viewedProducts;
//
//        // Best Seller with translation
//        try {
//            $bestSellingProducts = Product::where('status', 1)
//                ->orderBy('total_sales', 'desc')
//                ->limit(10)
//                ->get();
//
//            // Translate best sellers
//            $translatedBestSellers = $bestSellingProducts->map(function ($product) use ($lang) {
//                if ($product->lang != $lang) {
//                    $translationField = "translation_{$lang}";
//                    $translationId = $product->$translationField ?? null;
//
//                    if ($translationId) {
//                        $translatedProduct = Product::where('wordpress_id', $translationId)
//                            ->where('status', 1)
//                            ->first();
//
//                        if ($translatedProduct) {
//                            return $translatedProduct;
//                        }
//                    }
//                }
//                return $product;
//            })->filter()->values();
//
//            $data['best_seller'] = $translatedBestSellers->map(function ($product) use ($lang) {
//                return productArrivalResource::make($product)->additional(['lang' => $lang]);
//            });
//        } catch (\Exception $e) {
//            $data['best_seller'] = [];
//        }
//
//        // Recommended (same as best seller in your case)
//        try {
//            $data['recommended'] = $data['best_seller'];
//        } catch (\Exception $e) {
//            $data['recommended'] = [];
//        }
//
//        return $this->responseMsg(
//            'data has been returned successfully',
//            $data,
//            200
//        );
//    }




    public function products_variety(Request $request)
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        } else {
            $lang = 'en';
        }

        $viewedProducts = [];

        if ($request->has('viewed_product_ids')) {
            $viewedProductIds = [];
            foreach ($request->viewed_product_ids as $key => $viewedProductId) {
                $viewedProductIds[] = $viewedProductId;
            }

            $products = Product::whereIn('wordpress_id', $viewedProductIds)
                ->where('slug', '!=', null)
                ->get();

            // Translate viewed products
            $translatedViewedProducts = $products->map(function ($product) use ($lang) {
                if ($product->lang != $lang) {
                    $translationField = "translation_{$lang}";
                    $translationId = $product->$translationField ?? null;

                    if ($translationId) {
                        $translatedProduct = Product::where('wordpress_id', $translationId)
                            ->where('status', 1)
                            ->whereNotNull('slug')
                            ->first();

                        if ($translatedProduct) {
                            return $translatedProduct;
                        }
                    }
                }
                return $product;
            })->filter()->values();

            // Remove duplicates by wordpress_id
            $uniqueViewedProducts = $translatedViewedProducts->unique('wordpress_id');

            $viewedProducts = $uniqueViewedProducts->map(function ($product) use ($lang) {
                return productArrivalResource::make($product)->additional(['lang' => $lang]);
            });
        }

        $data['recent_viewed'] = $viewedProducts;

        // Best Seller with translation - FIXED
        try {
            $bestSellingProducts = Product::where('lang', $lang) // Critical: filter by language
            ->where('status', 1)
                ->where('current_stock', '>=', 1)
                ->orderBy('total_sales', 'desc')
                ->limit(10)
                ->get();

            $data['best_seller'] = $bestSellingProducts->map(function ($product) use ($lang) {
                return productArrivalResource::make($product)->additional(['lang' => $lang]);
            });
        } catch (\Exception $e) {
            $data['best_seller'] = [];
        }

        // Recommended - Completely different approach to avoid duplicates
        try {
            // Get products that are NOT in best sellers
            $bestSellerIds = $bestSellingProducts->pluck('wordpress_id')->toArray();

            $recommendedProducts = Product::where('lang', $lang) // Add language filter
            ->where('status', 1)
                ->where('current_stock', '>=', 1)
                ->whereNotIn('wordpress_id', $bestSellerIds) // Exclude best sellers
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $data['recommended'] = $recommendedProducts->map(function ($product) use ($lang) {
                return productArrivalResource::make($product)->additional(['lang' => $lang]);
            });
        } catch (\Exception $e) {
            $data['recommended'] = [];
        }

        return $this->responseMsg(
            'data has been returned successfully',
            $data,
            200
        );
    }







//    public function getCategory(Request $request): JsonResponse
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        }
//        $categories_per_page = $request->input('categories_per_page', null); // Changed default to null
//        $page_number = $request->input('page_number', 1);
//
//        $query = Category::where('lang',$lang)->query();
//
//        // Apply filtering
//        $query->where(function ($q) use ($request) {
//            if ($request->has('is_wholesale') && $request->is_wholesale == 1) {
//                $q->where('category_type', 'whole_sale');
//            } else {
//                $q->whereNull('category_type')->orWhere('category_type', '!=', 'whole_sale');
//            }
//        });
//
//        // Handle both paginated and non-paginated cases
//        if ($categories_per_page == null) {
//            $categories = $query->get();
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => null,
//                'categories_per_page' => null,
//                'categories' => CategoryResource::collection($categories),
//            ], 200);
//        } else {
//            $categories = $query->paginate($categories_per_page, ['*'], 'page', $page_number);
//            return $this->responseMsg('data has been returned successfully', [
//                'pagination' => [
//                    'total' => $categories->total(),
//                    'per_page' => $categories->perPage(),
//                    'current_page' => $categories->currentPage(),
//                    'next_page_url' => $categories->nextPageUrl(),
//                    'prev_page_url' => $categories->previousPageUrl(),
//                    'from' => $categories->firstItem(),
//                    'to' => $categories->lastItem(),
//                ],
//                'categories_per_page' => $categories_per_page,
//                'categories' => CategoryResource::collection($categories),
//            ], 200);
//        }
//    }



    public function getCategory(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language');
        $lang = $lang && Str::startsWith($lang, 'ar') ? 'ar' : 'en';

        $categoriesPerPage = $request->input('categories_per_page', null);
        $pageNumber = $request->input('page_number', 1);

        // Base query
        $query = Category::where('lang', $lang);
        $query=$query->where('home_status','=',1);

        // Apply filtering
//        if ((int)$request->input('is_wholesale', 0) === 1) {
//            $query->where('category_type', 'whole_sale');
//        } else {
//            $query->where(function ($q) {
//                $q->whereNull('category_type')
//                    ->orWhere('category_type', '!=', 'whole_sale');
//            });
//        }

        // Handle pagination
//        if ($categoriesPerPage === null) {
            $categories = $query->get();
//            dd($categories);
        $parentCategories = $categories->where('parent_id', 0);
        $categories_returned = CategoryResource::collection($parentCategories);

        return $this->responseMsg(
            'Data has been returned successfully',
            $categories_returned,
            200
        );
//        } else {
//            $categories = $query->paginate($categoriesPerPage, ['*'], 'page', $pageNumber);
//            return $this->responseMsg('Data has been returned successfully', [
//                'pagination' => [
//                    'total' => $categories->total(),
//                    'per_page' => $categories->perPage(),
//                    'current_page' => $categories->currentPage(),
//                    'next_page_url' => $categories->nextPageUrl(),
//                    'prev_page_url' => $categories->previousPageUrl(),
//                    'from' => $categories->firstItem(),
//                    'to' => $categories->lastItem(),
//                ],
//                'categories_per_page' => $categoriesPerPage,
//                'categories' => CategoryResource::collection($categories),
//            ], 200);
//        }
    }





    protected function search_products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);

        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search(base64_encode($request['name']), 'all', $request['limit'], $request['offset']);
        }
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return $this->responseMsg("the searched products has been returned successfully", ProductArrivalResource::collection($products['products']), 200);
    }


    public function get_latest_products(Request $request): JsonResponse
    {
        // dd(auth('api')->user());
        $products = ProductManager::get_latest_products($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return $this->responseMsg("the latest products has been returned successfully", productArrivalResource::collection($products['products']), 200);
    }

    public function getNewArrivalProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getNewArrivalProducts($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return $this->responseMsg("the new arrival products has been returned successfully", productArrivalResource::collection($productsList), 200);
    }


    public function getFeaturedProductsList(Request $request): JsonResponse
    {
        $products = ProductManager::getFeaturedProductsList($request, $request['limit'], $request['offset']);
        // $products['products'] = Helpers::product_data_formatting($products['products'], true);
        // dd( $products['products']);


        return $this->responseMsg("the featured products has been returned successfully", productArrivalResource::collection($products['products']), 200);
    }

    public function getTopRatedProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getTopRatedProducts($request, $request['limit'], $request['offset']);
        $productsList = count($products->items()) > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return $this->responseMsg("the top rated products has been returned successfully", productArrivalResource::collection($productsList), 200);
    }


    public function get_searched_products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);

        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search(base64_encode($request['name']), 'all', $request['limit'], $request['offset']);
        }
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return $this->responseMsg("the searched products has been returned successfully", productArrivalResource::collection($products['products']), 200);
    }

    public function getProductsFilter(Request $request): JsonResponse
    {
        $search = [base64_decode($request->search)];
        $categories = json_decode($request->category);
        $brand = json_decode($request->brand);
        $publishingHouses = $request->has('publishing_houses') ? json_decode($request['publishing_houses']) : [];
        $productAuthors = $request->has('product_authors') ? json_decode($request['product_authors']) : [];

        $publishingHouseList = PublishingHouse::with(['publishingHouseProducts'])
            ->whereHas('publishingHouseProducts.product', function ($query) {
                return $query->active();
            })
            ->withCount(['publishingHouseProducts' => function ($query) {
                return $query->whereHas('product', function ($query) {
                    return $query->active();
                });
            }])->get();

        $productIdsForPublisher = [];
        $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIdsForPublisher) {
            $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIdsForPublisher) {
                $productIdsForPublisher[] = $publishingHouse->product_id;
            });
        });

        $productIdsForUnknownPublisher = Product::active()->with(['clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForPublisher)->pluck('id')->toArray();

        $authorList = Author::withCount(['digitalProductAuthor' => function ($query) {
            return $query->whereHas('product', function ($query) {
                return $query->active();
            });
        }])->get();

        $productIdsForAuthor = [];
        $authorList->each(function ($authorGroup) use (&$productIdsForAuthor) {
            $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIdsForAuthor) {
                $productIdsForAuthor[] = $authorItem->product_id;
            });
        });
        $productIdsForUnknownAuthor = Product::active()->with(['clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForAuthor)->pluck('id')->toArray();

        $productsIDArray = [];
        if ($request->has('search') && !empty($request['search'])) {
            $productsIDArray = [0];
            $searchProducts = ProductManager::search_products($request, base64_decode($request->search), 'all', $request['limit'], $request['offset']);
            if ($searchProducts['products'] == null || app()->getLocale() !== 'en') {
                $searchProducts = ProductManager::translated_product_search($request->search, 'all', $request['limit'], $request['offset']);
            }
            if ($searchProducts['products']) {
                foreach ($searchProducts['products'] as $product) {
                    $productsIDArray[] = $product->id;
                }
            }
        }

        $categoryList = Category::where(['position' => 0])->whereIn('id', $categories)->pluck('id')->toArray();
        $subCategoryIds = Category::where(['position' => 1])->whereIn('id', $categories)->pluck('id')->toArray();
        $subSubCategoryIds = Category::where(['position' => 2])->whereIn('id', $categories)->pluck('id')->toArray();

        // Products search
        $products = Product::active()->with(['rating', 'tags', 'clearanceSale' => function ($query) {
            return $query->active();
        }])
            ->when(!empty($productsIDArray), function ($query) use ($productsIDArray) {
                return $query->whereIn('id', $productsIDArray);
            })
            ->withCount(['reviews' => function ($query) {
                $query->active()->whereNull('delivery_man_id');
            }])
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })
            ->when($request->has('brand') && count($brand) > 0, function ($query) use ($request, $brand) {
                return $query->whereIn('brand_id', $brand);
            })
            ->when($request->has('category') && count($categoryList) > 0, function ($query) use ($categoryList, $subCategoryIds, $subSubCategoryIds) {
                return $query->whereIn('category_id', $categoryList)
                    ->when(count($subCategoryIds) > 0, function ($query) use ($subCategoryIds) {
                        return $query->whereIn('sub_category_id', $subCategoryIds);
                    })->when(count($subSubCategoryIds) > 0, function ($query) use ($subSubCategoryIds) {
                        return $query->whereIn('sub_sub_category_id', $subSubCategoryIds);
                    });
            })
            ->when($request->has('publishing_houses') && $publishingHouses, function ($query) use ($request, $publishingHouses, $productIdsForUnknownPublisher) {
                $publishingHouseList = PublishingHouse::whereIn('id', $publishingHouses)->with(['publishingHouseProducts'])->withCount(['publishingHouseProducts' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $publishingHouseProductIds = [];
                $publishingHouseList->each(function ($publishingHouseGroup) use (&$publishingHouseProductIds) {
                    $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$publishingHouseProductIds) {
                        $publishingHouseProductIds[] = $publishingHouse->product_id;
                    });
                });

                if (in_array(0, $publishingHouses)) {
                    $publishingHouseProductIds = array_merge($publishingHouseProductIds, $productIdsForUnknownPublisher);
                }

                return $query->where(['product_type' => 'digital'])->whereIn('id', $publishingHouseProductIds);
            })
            ->when($request->has('product_authors') && $productAuthors, function ($query) use ($request, $productAuthors, $productIdsForUnknownAuthor) {
                $authorList = Author::whereIn('id', $productAuthors)->withCount(['digitalProductAuthor' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $authorProductIds = [];
                $authorList->each(function ($authorGroup) use (&$authorProductIds) {
                    $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$authorProductIds) {
                        $authorProductIds[] = $authorItem->product_id;
                    });
                });
                if (in_array(0, $productAuthors)) {
                    $authorProductIds = array_merge($authorProductIds, $productIdsForUnknownAuthor);
                }
                return $query->where(['product_type' => 'digital'])->whereIn('id', $authorProductIds);
            })
            ->when($request->has('sort_by') && !empty($request->sort_by), function ($query) use ($request) {
                $query->when($request['sort_by'] == 'low-high', function ($query) {
                    return $query->orderBy('unit_price', 'ASC');
                })
                    ->when($request['sort_by'] == 'high-low', function ($query) {
                        return $query->orderBy('unit_price', 'DESC');
                    })
                    ->when($request['sort_by'] == 'a-z', function ($query) {
                        return $query->orderBy('name', 'ASC');
                    })
                    ->when($request['sort_by'] == 'z-a', function ($query) {
                        return $query->orderBy('name', 'DESC');
                    })
                    ->when($request['sort_by'] == 'latest', function ($query) {
                        return $query->latest();
                    });
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when(!empty($request['price_min']) || !empty($request['price_max']), function ($query) use ($request) {
                return $query->whereBetween('unit_price', [$request['price_min'], $request['price_max']]);
            });

        if (request('offer_type') == 'clearance_sale') {
            $products = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $products, dataLimit: $request['limit'], offset: $request['offset']);
        } else {
            $products = ProductManager::getPriorityWiseSearchedProductQuery(query: $products, keyword: implode(' ', $search), dataLimit: $request['limit'], offset: $request['offset'], type: 'searched');
        }

        return response()->json([
            'total_size' => $products->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'min_price' => $products->min('unit_price'),
            'max_price' => $products->max('unit_price'),
            'products' => count($products) > 0 ? Helpers::product_data_formatting($products->items(), true) : [],
        ]);
    }

    public function get_suggestion_product(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);
        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search(base64_encode($request['name']), 'all', $request['limit'], $request['offset']);
        }

        $products_array = [];
        if ($products['products']) {
            foreach ($products['products'] as $product) {
                $products_array[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }
        }

        return response()->json(['products' => $products_array], 200);
    }

//    public function getProductDetails(Request $request, $slug): JsonResponse
//    {
//        $user = Helpers::getCustomerInformation($request);
////        dd('fghjk');
//
//        $product = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
//            return $query->active();
//        }])
//            ->withCount(['wishList' => function ($query) use ($user) {
//                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
//            }])
//            ->where(['slug' => $slug])->first();
//
//        if (isset($product)) {
//            $restockRequestedIds = $this->restockProductRepo->getListWhere(filters: ['product_id' => $product['id']], dataLimit: 'all')?->pluck('id')->toArray() ?? [];
//
//            $product = Helpers::product_data_formatting($product, false);
//            if (isset($product->reviews) && !empty($product->reviews)) {
//                $overallRating = getOverallRating($product->reviews);
//                $product['average_review'] = $overallRating[0];
//            } else {
//                $product['average_review'] = 0;
//            }
//            $temporary_close = getWebConfig(name: 'temporary_close');
//            $inhouse_vacation = getWebConfig(name: 'vacation_add');
//            $inhouse_vacation_start_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
//            $inhouse_vacation_end_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
//            $inhouse_temporary_close = $product['added_by'] == 'admin' ? $temporary_close['status'] : false;
//            $product['inhouse_vacation_start_date'] = $inhouse_vacation_start_date;
//            $product['inhouse_vacation_end_date'] = $inhouse_vacation_end_date;
//            $product['inhouse_temporary_close'] = $inhouse_temporary_close;
//            $product['reviews_count'] = $product->reviews->count();
//            $product['digital_product_authors_names'] = $this->productService->getProductAuthorsInfo(product: $product)['names'];
//            $product['digital_product_publishing_house_names'] = $this->productService->getProductPublishingHouseInfo(product: $product)['names'];
//
//            if ($user != 'offline' && count($restockRequestedIds) > 0) {
//
//                $restockCustomerRequestedList = $this->restockProductCustomerRepo->getListWhere(
//                    filters: ['customer_id' => $user->id, 'restock_product_ids' => $restockRequestedIds]
//                )->pluck('variant')->toArray();
//
//                $product['restock_requested_list'] = $restockCustomerRequestedList;
//                $product['is_restock_requested'] = count($restockCustomerRequestedList) > 0 ? 1 : 0;
//            } else {
//                $product['restock_requested_list'] = [];
//                $product['is_restock_requested'] = 0;
//            }
//        }
//        return response()->json($product, 200);
//    }



    public function getProductDetails(Request $request, $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        }
        $user = Helpers::getCustomerInformation($request);

        $product = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
            return $query->active();
        }])
        ->withCount(['wishList' => function ($query) use ($user) {
            $query->where('customer_id', $user != 'offline' ? $user->id : '0');
        }])
        ->where(['slug' => $slug])->first();
        if (isset($product)) {
            $restockRequestedIds = $this->restockProductRepo->getListWhere(filters: ['product_id' => $product['id']], dataLimit: 'all')?->pluck('id')->toArray() ?? [];

            $product = Helpers::product_data_formatting($product, false);

            // Get buy-together products
            $buy_together_meta = $product->meta_data->firstWhere('key', 'woodmart_fbt_products');
            $buy_together = [];

            if ($buy_together_meta && !empty($buy_together_meta->value)) {
                $buy_together = is_string($buy_together_meta->value)
                    ? json_decode($buy_together_meta->value, true)
                    : $buy_together_meta->value;
                $buy_together = is_array($buy_together) ? $buy_together : [];
            }

            $product['buy_it_together'] = $this->processBuyTogether($buy_together, $product->id);

            if (isset($product->reviews) && !empty($product->reviews)) {
                $overallRating = getOverallRating($product->reviews);
                $product['average_review'] = $overallRating[0];
            } else {
                $product['average_review'] = 0;
            }

            $temporary_close = getWebConfig(name: 'temporary_close');
            $inhouse_vacation = getWebConfig(name: 'vacation_add');
            $inhouse_vacation_start_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
            $inhouse_vacation_end_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
            $inhouse_temporary_close = $product['added_by'] == 'admin' ? $temporary_close['status'] : false;
            $product['inhouse_vacation_start_date'] = $inhouse_vacation_start_date;
            $product['inhouse_vacation_end_date'] = $inhouse_vacation_end_date;
            $product['inhouse_temporary_close'] = $inhouse_temporary_close;
            $product['reviews_count'] = $product->reviews->count();
            $product['digital_product_authors_names'] = $this->productService->getProductAuthorsInfo(product: $product)['names'];
            $product['digital_product_publishing_house_names'] = $this->productService->getProductPublishingHouseInfo(product: $product)['names'];

            if ($user != 'offline' && count($restockRequestedIds) > 0) {
                $restockCustomerRequestedList = $this->restockProductCustomerRepo->getListWhere(
                    filters: ['customer_id' => $user->id, 'restock_product_ids' => $restockRequestedIds]
                )->pluck('variant')->toArray();

                $product['restock_requested_list'] = $restockCustomerRequestedList;
                $product['is_restock_requested'] = count($restockCustomerRequestedList) > 0 ? 1 : 0;
            } else {
                $product['restock_requested_list'] = [];
                $product['is_restock_requested'] = 0;
            }
        }

        return response()->json($product, 200);
    }




//    public function getProductDetails(Request $request, $slug): JsonResponse
//    {
//        $lang = $request->header('Accept-Language');
////        dd($lang);
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        } else {
//            $lang = 'en';
//        }
//
//        $user = Helpers::getCustomerInformation($request);
//
//        $product = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
//            return $query->active();
//        }])
//            ->withCount(['wishList' => function ($query) use ($user) {
//                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
//            }])
//            ->where(['slug' => $slug])->first();
//
//        // Handle translation if product found
//        if (isset($product)) {
//            // Check if product needs translation
//            if ($product->lang != $lang) {
//                $translationField = "translation_{$lang}";
//                $translationId = $product->$translationField ?? null;
//
//                if ($translationId) {
//                    // Find translated product
//                    $translatedProduct = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
//                        return $query->active();
//                    }])
//                        ->withCount(['wishList' => function ($query) use ($user) {
//                            $query->where('customer_id', $user != 'offline' ? $user->id : '0');
//                        }])
//                        ->where('wordpress_id', $translationId)
//                        ->first();
//
//                    // Use translated product if found and valid
//                    if ($translatedProduct && $translatedProduct->status == 1) {
//                        $product = $translatedProduct;
//                    }
//                }
//            }
//
//            $restockRequestedIds = $this->restockProductRepo->getListWhere(filters: ['product_id' => $product['id']], dataLimit: 'all')?->pluck('id')->toArray() ?? [];
//
//            $product = Helpers::product_data_formatting($product, false);
//
//            // Get buy-together products
//            $buy_together_meta = $product->meta_data->firstWhere('key', 'woodmart_fbt_products');
//            $buy_together = [];
//
//            if ($buy_together_meta && !empty($buy_together_meta->value)) {
//                $buy_together = is_string($buy_together_meta->value)
//                    ? json_decode($buy_together_meta->value, true)
//                    : $buy_together_meta->value;
//                $buy_together = is_array($buy_together) ? $buy_together : [];
//            }
//
//            $product['buy_it_together'] = $this->processBuyTogether($buy_together, $product->id, $lang);
//
//            if (isset($product->reviews) && !empty($product->reviews)) {
//                $overallRating = getOverallRating($product->reviews);
//                $product['average_review'] = $overallRating[0];
//            } else {
//                $product['average_review'] = 0;
//            }
//
//            $temporary_close = getWebConfig(name: 'temporary_close');
//            $inhouse_vacation = getWebConfig(name: 'vacation_add');
//            $inhouse_vacation_start_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
//            $inhouse_vacation_end_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
//            $inhouse_temporary_close = $product['added_by'] == 'admin' ? $temporary_close['status'] : false;
//            $product['inhouse_vacation_start_date'] = $inhouse_vacation_start_date;
//            $product['inhouse_vacation_end_date'] = $inhouse_vacation_end_date;
//            $product['inhouse_temporary_close'] = $inhouse_temporary_close;
//            $product['reviews_count'] = $product->reviews->count();
//            $product['digital_product_authors_names'] = $this->productService->getProductAuthorsInfo(product: $product)['names'];
//            $product['digital_product_publishing_house_names'] = $this->productService->getProductPublishingHouseInfo(product: $product)['names'];
//
//            if ($user != 'offline' && count($restockRequestedIds) > 0) {
//                $restockCustomerRequestedList = $this->restockProductCustomerRepo->getListWhere(
//                    filters: ['customer_id' => $user->id, 'restock_product_ids' => $restockRequestedIds]
//                )->pluck('variant')->toArray();
//
//                $product['restock_requested_list'] = $restockCustomerRequestedList;
//                $product['is_restock_requested'] = count($restockCustomerRequestedList) > 0 ? 1 : 0;
//            } else {
//                $product['restock_requested_list'] = [];
//                $product['is_restock_requested'] = 0;
//            }
//        }
//
//        return response()->json($product, 200);
//    }




//    private function processBuyTogether(Request $request,$buy_together, $product_id)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        }
//        if (empty($buy_together)) {
//            return [];
//        }
//
//        $buy_together_ids = collect($buy_together)->pluck('woodmart_fbt_product_id')->filter()->toArray();
//
//        if (empty($buy_together_ids)) {
//            return [];
//        }
//
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $buy_together_ids)->get();
//        $buy_together_products = Product::where('lang',$lang)->whereIn('wordpress_id', $buy_together_ids)->get();
//
//        $discounts_map = $buy_together->pluck('woodmart_fbt_product_discount', 'woodmart_fbt_product_id')->toArray();
//        $main_discounts_map = $buy_together->pluck('woodmart_main_products_discount', 'woodmart_fbt_product_id')->toArray();
//
//        $buy_it_together = collect();
//
//        // Process products
//        foreach ($buy_together_products as $product) {
//            $product_discount = $discounts_map[$product->wordpress_id] ?? null;
//            $main_product_discount = $main_discounts_map[$product->wordpress_id] ?? null;
//
//            $buy_it_together->push(
//                productArrivalResource::make($product)->additional([
//                    'buy_together' => true,
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'product_main_discount' => $main_product_discount,
//                ])
//            );
//        }
//
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            $product_discount = $discounts_map[$variation->wordpress_id] ?? null;
//            $main_product_discount = $main_discounts_map[$variation->wordpress_id] ?? null;
//
//            $parentProduct = Product::where('wordpress_id', $variation->product_id)->first();
//
//            if ($parentProduct) {
//                $buy_it_together->push(
//                    productArrivalResource::make($parentProduct)->additional([
//                        'buy_together' => true,
//                        'variation_id' => $variation->wordpress_id,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => $main_product_discount,
//                    ])
//                );
//            } else {
//                // Fallback for variations without parent products
//                $buy_it_together->push([
//                    'id' => $variation->wordpress_id,
//                    'name' => null,
//                    'price_before_discount' => (float) $variation->regular_price ?? 0,
//                    'price_after_discount' => (float) $variation->price ?? 0,
//                    'thumbnail' => $variation->image ?? null,
//                    'images' => $variation->image ? [$variation->image] : [],
//                    'in_wishlist' => false,
//                    'slug' => null,
//                    'product_stock_count' => 0,
//                    'discount' => 0,
//                    'is_taxable' => false,
//                    'main_product_discount' => $main_product_discount,
//                    'buy_together_discount' => $product_discount,
//                    'variant' => DigitalProductVariationResource::make($variation),
//                    'variants' => []
//                ]);
//            }
//        }
//
//        return $buy_it_together->values()->toArray();
//    }





    private function processBuyTogether(Request $request, $buy_together, $product_id)
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        } else {
            $lang = 'en';
        }

        if (empty($buy_together)) {
            return [];
        }

        $buy_together_ids = collect($buy_together)->pluck('woodmart_fbt_product_id')->filter()->toArray();

        if (empty($buy_together_ids)) {
            return [];
        }

        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $buy_together_ids)->get();
        $buy_together_products = Product::whereIn('wordpress_id', $buy_together_ids)->get();

        $discounts_map = $buy_together->pluck('woodmart_fbt_product_discount', 'woodmart_fbt_product_id')->toArray();
        $main_discounts_map = $buy_together->pluck('woodmart_main_products_discount', 'woodmart_fbt_product_id')->toArray();

        $buy_it_together = collect();

        // Process products with translation
        foreach ($buy_together_products as $product) {
            // Handle translation
            if ($product->lang != $lang) {
                $translationField = "translation_{$lang}";
                $translationId = $product->$translationField ?? null;

                if ($translationId) {
                    $translatedProduct = Product::where('wordpress_id', $translationId)
                        ->where('status', 1)
                        ->first();

                    if ($translatedProduct) {
                        $product = $translatedProduct;
                    }
                }
            }

            $product_discount = $discounts_map[$product->wordpress_id] ?? null;
            $main_product_discount = $main_discounts_map[$product->wordpress_id] ?? null;

            $buy_it_together->push(
                productArrivalResource::make($product)->additional([
                    'buy_together' => true,
                    'variation_id' => null,
                    'product_discount' => $product_discount,
                    'product_main_discount' => $main_product_discount,
                    'lang' => $lang,
                ])
            );
        }

        // Process variations with translation
        foreach ($buy_together_variations as $variation) {
            $product_discount = $discounts_map[$variation->wordpress_id] ?? null;
            $main_product_discount = $main_discounts_map[$variation->wordpress_id] ?? null;

            $parentProduct = Product::where('wordpress_id', $variation->product_id)->first();

            if ($parentProduct) {
                // Handle translation for parent product
                if ($parentProduct->lang != $lang) {
                    $translationField = "translation_{$lang}";
                    $translationId = $parentProduct->$translationField ?? null;

                    if ($translationId) {
                        $translatedParentProduct = Product::where('wordpress_id', $translationId)
                            ->where('status', 1)
                            ->first();

                        if ($translatedParentProduct) {
                            $parentProduct = $translatedParentProduct;

                            // Try to find matching variation in translated product
                            $translatedVariation = DigitalProductVariation::where('product_id', $translatedParentProduct->wordpress_id)
                                ->where('attributes', $variation->attributes)
                                ->first();

                            if ($translatedVariation) {
                                $variation = $translatedVariation;
                            }
                        }
                    }
                }

                $buy_it_together->push(
                    productArrivalResource::make($parentProduct)->additional([
                        'buy_together' => true,
                        'variation_id' => $variation->wordpress_id,
                        'product_discount' => $product_discount,
                        'product_main_discount' => $main_product_discount,
                        'lang' => $lang,
                    ])
                );
            } else {
                // Fallback for variations without parent products
                $buy_it_together->push([
                    'id' => $variation->wordpress_id,
                    'name' => null,
                    'price_before_discount' => (float) $variation->regular_price ?? 0,
                    'price_after_discount' => (float) $variation->price ?? 0,
                    'thumbnail' => $variation->image ?? null,
                    'images' => $variation->image ? [$variation->image] : [],
                    'in_wishlist' => false,
                    'slug' => null,
                    'product_stock_count' => 0,
                    'discount' => 0,
                    'is_taxable' => false,
                    'main_product_discount' => $main_product_discount,
                    'buy_together_discount' => $product_discount,
                    'variant' => DigitalProductVariationResource::make($variation),
                    'variants' => []
                ]);
            }
        }

        return $buy_it_together->values()->toArray();
    }






    public function getBestSellingProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }


    public function get_home_categories(Request $request)
    {
        $cacheKey = 'cache_home_categories_api_list' . (str_replace(' ', '_', strtolower(app()->getLocale())));
        $cacheKeys = Cache::get(CACHE_HOME_CATEGORIES_API_LIST, []);
        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put(CACHE_HOME_CATEGORIES_API_LIST, $cacheKeys, CACHE_FOR_3_HOURS);
        }

        $categories = Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($request) {
            $getCategories = Category::whereHas('product', function ($query) {
                return $query->active()->with(['clearanceSale' => function ($query) {
                    return $query->active();
                }]);
            })->where('home_status', true)->get();

            $getCategories->map(function ($data) use ($request) {
                $data['products'] = Helpers::product_data_formatting(CategoryManager::products($data['id'], $request, 8), true);
                return $data;
            });
            return $getCategories;
        });
        return response()->json($categories, 200);
    }

    public function get_related_products(Request $request, $id)
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        }
        if (Product::find($id)) {
            $products = ProductManager::get_related_products($id, $request);
            $products = Helpers::product_data_formatting($products, true);

            return $this->responseMsg('Related products fetched successfully.', $products->map(function ($product) use ($lang) {
                return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
            }), 200);
        }
        return $this->responseMsg([
            'errors' => ['code' => 'product-001', 'message' => translate('product_not_found')]
        ], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer', 'reply'])->where(['product_id' => $id])->get();
        foreach ($reviews as $item) {
            $item['attachment_full_url'] = $item->attachment_full_url;
        }
        return $this->responseMsg("product reviews has been returned successfully", $reviews, 200);
    }

    public function getProductReviewByOrder(Request $request, $productId, $orderId): JsonResponse
    {
        $user = $request->user();
        $reviews = Review::with('reply')->where(['product_id' => $productId, 'customer_id' => $user->id])->whereNull('delivery_man_id')->get();
        $reviewData = null;
        foreach ($reviews as $review) {
            if ($review->order_id == $orderId) {
                $reviewData = $review;
            }
        }
        if (isset($reviews[0]) && !$reviewData) {
            $reviewData = ($reviews[0]['order_id'] == null) ? $reviews[0] : null;
        }
        if ($reviewData) {
            $reviewData['attachment_full_url'] = $reviewData->attachment_full_url;
        }

        return response()->json($reviewData ?? [], 200);
    }

    public function deleteReviewImage(Request $request): JsonResponse
    {
        $review = Review::find($request['id']);

        $array = [];
        foreach ($review->attachment as $image) {
            $imageName = $image['file_name'] ?? $image;
            if ($imageName != $request['name']) {
                $array[] = $image;
            } else {
                $this->delete(filePath: 'review/' . $request['name']);
            }
        }

        $review->attachment = $array;
        $review->save();
        return response()->json(translate('review_image_removed_successfully'), 200);
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = getOverallRating($product->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function counter($product_id)
    {
        try {
            $countOrder = OrderDetail::where('product_id', $product_id)->count();
            $countWishlist = Wishlist::where('product_id', $product_id)->count();
            return response()->json(['order_count' => $countOrder, 'wishlist_count' => $countWishlist], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function socialShareLink($product_id): JsonResponse
    {
        $product = Product::where('slug', $product_id)->first();
        try {
            $link = route('product', $product->slug);
            return response()->json($link, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submitProductReview(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            // 'order_id' => 'required',
            'comment' => 'required',
//            'rating' => 'required|numeric|between:1,5|regex:/^\d*\.\d{1}$/',
            'rating' => ['required', 'numeric', 'between:1,5', 'regex:/^\d+(\.\d+)?$/'],

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }
        // $image_array = [];
        // if (!empty($request->file('fileUpload'))) {
        //     foreach ($request->file('fileUpload') as $image) {
        //         if ($image != null) {
        //             $image_array[] = [
        //                 'file_name' => $this->upload('review/', 'webp', $image),
        //                 'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
        //             ];
        //         }
        //     }
        // }

        //check if this rate exists before or not if it does update it else create a new one
        $reviewData = Review::where([
            'customer_id' => $request->user()->id,
            'product_id' => $request['product_id'],
        ])->first();



        if ($reviewData) {
            $reviewData->update([
                'comment' => $request['comment'],
                'rating' => round($request['rating'], 1),
                // 'attachment' => $image_array,
            ]);
        } else {
            $reviewData = Review::create([
                'customer_id' => $request->user()->id,
                'product_id' => $request['product_id'],
                'comment' => $request['comment'],
                'rating' => round($request['rating'], 1),
                // 'attachment' => $image_array,
            ]);
        }
        // dd($reviewData);

        $averageRating = Review::where('product_id', $reviewData->product_id)
            ->selectRaw('avg(rating) as average')
            ->value('average');

        Product::where('id', $reviewData->product_id)->update([
            'average_rating' => round($averageRating, 1),
        ]);


//        $this->syncProductReviewToWooCommerce($reviewData);
        return $this->responseMsg('the product has been rated successfully',null,200);
    }


    protected function syncProductReviewToWooCommerce($rate)
    {
        // try {
            // Prepare the API endpoint and credentials
            $endpoint = config('services.woocommerce.url') . '/wp-json/wc/v3/products/reviews';
            $consumerKey = config('services.woocommerce.key');
            $consumerSecret = config('services.woocommerce.secret');

            if (empty($endpoint) || empty($consumerKey) || empty($consumerSecret)) {
                throw new \Exception('WooCommerce API credentials not configured');
            }

            $validator = Validator::make($rate->toArray(), [
                'product_id' => 'required|exists:products,wordpress_id',
                'comment' => 'required|string',
                'rating' => 'required|numeric|between:1,5'
            ]);

            if ($validator->fails()) {
                throw new \Exception('Invalid data for syncing product review to WooCommerce');
            }

            $user = User::findOrFail($rate->customer_id);
            $rateData = [
                "product_id" => $rate->product_id,
                "review" => $rate->comment,
                "reviewer" => $user->name ?? 'Anonymous',
                "reviewer_email" => $user['email'] ?? 'no-email@example.com',
                "rating" => $rate->rating,
            ];

            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($consumerKey, $consumerSecret)
                    ->timeout(30)
                    ->retry(3, 100)
                    ->post($endpoint, $rateData);

                if ($response->failed()) {
                    throw new \Illuminate\Http\Client\RequestException($response);
                }
            } catch (\Illuminate\Http\Client\RequestException $e) {
                throw new \Exception("HTTP request returned status code {$e->response->status()}:\n" . $e->response->body());
            }
            if ($response->successful()) {
                $woocommerceRate = $response->json();
                $rate->update(['wordpress_id' => $woocommerceRate['id']]);
                Log::info("rate #{$rate->id} synced to WooCommerce successfully");
                return true;
            }

            $errorDetails = [
                'rate_id' => $rate->id,
                'status' => $response->status(),
                'response' => $response->body(),
                'payload' => $rateData
            ];

            Log::error('WooCommerce sync failed', $errorDetails);
            throw new \Exception('WooCommerce rate creation failed');
        // } catch (\Exception $e) {
        //     Log::error('WooCommerce sync exception: ' . $e->getMessage(), [
        //         'rate_id' => $rate->id,
        //         'error' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString()
        //     ]);
        //     return false;
        // }
    }

    public function updateProductReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $review = Review::find($request['id']);
        $image_array = [];
        if ($review && $review->attachment && $request->has('fileUpload')) {
            foreach ($review->attachment as $image) {
                $image_array[] = $image;
            }
        }
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    $image_array[] = [
                        'file_name' => $this->upload('review/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }

        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = $image_array;
        $review->save();

        return response()->json(['message' => translate('successfully_review_updated')], 200);
    }

    public function submit_deliveryman_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $order = Order::where([
            'id' => $request->order_id,
            'customer_id' => $request->user()->id,
            'payment_status' => 'paid'
        ])->first();

        if (!isset($order->delivery_man_id)) {
            return response()->json(['message' => translate('invalid_review')], 403);
        }

        Review::updateOrCreate(
            [
                'delivery_man_id' => $order->delivery_man_id,
                'customer_id' => $request->user()->id,
                'order_id' => $order->id
            ],
            [
                'customer_id' => $request->user()->id,
                'order_id' => $order->id,
                'delivery_man_id' => $order->delivery_man_id,
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]
        );
    }

    public function getShippingZones()
    {
        $shippingZone =ShippingZoneResource::collection( ShippingZone::all());
        return $this->responseMsg("shipping zones has been returned successfully",$shippingZone,200);
    }
    public function getShippingZoneMethods(Request $request)
    {
        if($request->has('zone_id')) {
            $shippingZone =ShippingZoneMethodResource::collection( ShippingZoneMethod::where('shipping_zone_id',$request->zone_id)->get());
            return $this->responseMsg("shipping zones has been returned successfully",$shippingZone,200);
        }
        $shippingZone =ShippingZoneMethodResource::collection( ShippingZoneMethod::all());
        return $this->responseMsg("shipping zones has been returned successfully",$shippingZone,200);
    }

    public function getShippingZoneMethodsEnabled()
    {
        $shippingZoneMethod = ShippingZoneMethod::where('enabled', 1)
            ->where('shipping_zone_id', 2)
            ->get(['method_id', 'wordpress_id', 'min_amount'])
            ->toArray();
//        dd($shippingZoneMethod);
        return $this->responseMsg("shipping zones has been returned successfully",
             [
                 'flat_rate' => @$shippingZoneMethod[0]['method_id'] == 'flat_rate'?@$shippingZoneMethod[0]['min_amount']:(@$shippingZoneMethod[1]['method_id'] == 'flat_rate'?@$shippingZoneMethod[1]['min_amount']:0),
                 'free_shipping' => @$shippingZoneMethod[0]['method_id'] == 'free_shipping'?@$shippingZoneMethod[0]['min_amount']:(@$shippingZoneMethod[1]['method_id'] == 'free_shipping'?@$shippingZoneMethod[1]['min_amount']:0)
            ]
            , 200);

    }

    public function getShippingZoneLocations()
    {
        $shippingZone =ShippingZoneLocationResource::collection( ShippingZoneLocation::all());
        return $this->responseMsg("shipping zones has been returned successfully",$shippingZone,200);
    }

    public function get_discounted_product(Request $request)
    {
        $products = ProductManager::get_discounted_product($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function get_most_demanded_product(Request $request)
    {
        $user = Helpers::getCustomerInformation($request);
        $products = MostDemanded::where('status', 1)->with(['product' => function ($query) use ($user) {
            $query->withCount(['orderDetails', 'orderDelivered', 'reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);
        }])->whereHas('product', function ($query) {
            return $query->active();
        })->first();

        if ($products) {
            $products['banner'] = $products->banner ?? '';
            $products['product_id'] = $products->product['id'] ?? 0;
            $products['slug'] = $products->product['slug'] ?? '';
            $products['review_count'] = $products->product['reviews_count'] ?? 0;
            $products['order_count'] = $products->product['order_details_count'] ?? 0;
            $products['delivery_count'] = $products->product['order_delivered_count'] ?? 0;
            $products['wishlist_count'] = $products->product['wish_list_count'] ?? 0;

            unset($products->product['category_ids']);
            unset($products->product['images']);
            unset($products->product['details']);
            unset($products->product);
        } else {
            $products = [];
        }

        return response()->json($products, 200);
    }

    public function getShopAgainProduct(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($user != 'offline') {
            $products = Product::active()->with(['seller.shop', 'reviews', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->whereHas('orderDetails.order', function ($query) use ($request) {
                    $query->where(['customer_id' => $request->user()->id]);
                })
                ->select('id', 'name', 'slug', 'thumbnail', 'unit_price', 'purchase_price', 'added_by', 'user_id')
                ->inRandomOrder()->take(12)->get();

            $products?->map(function ($product) {
                $product['reviews_count'] = $product->reviews->count();
                unset($product->reviews);
                return $product;
            });
        } else {
            $products = [];
        }


        return response()->json($products, 200);
    }

    public function just_for_you(Request $request)
    {
        $user = Helpers::getCustomerInformation($request);
        $limit = (int)($request['limit'] ?? 8);
        $offset = (int)($request['offset'] ?? 1);

        if ($user != 'offline') {
            $orders = $this->order->where(['customer_id' => $user->id])->with(['details'])->get();

            if ($orders) {
                $orders = $orders?->map(function ($order) {
                    $order_details = $order->details->map(function ($detail) {
                        $product = json_decode($detail->product_details);
                        $category = json_decode($product->category_ids)[0]->id;
                        $detail['category_id'] = $category;
                        return $detail;
                    });
                    $order['id'] = $order_details[0]->id;
                    $order['category_id'] = $order_details[0]->category_id;

                    return $order;
                });

                $categories = [];
                foreach ($orders as $order) {
                    $categories[] = ($order['category_id']);;
                }
                $ids = array_unique($categories);

                $products = $this->product->with([
                    'compareList' => function ($query) use ($user) {
                        return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                    },
                    'clearanceSale' => function ($query) {
                        return $query->active();
                    }
                ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->where(function ($query) use ($ids) {
                        foreach ($ids as $id) {
                            $query->orWhere('category_ids', 'like', "%{$id}%");
                        }
                    })
                    ->inRandomOrder()
                    ->paginate($limit, ['*'], 'page', $offset);
            } else {
                $products = $this->product->with([
                    'compareList' => function ($query) use ($user) {
                        return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                    },
                    'clearanceSale' => function ($query) {
                        return $query->active();
                    }
                ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->inRandomOrder()
                    ->paginate($limit, ['*'], 'page', $offset);
            }
        } else {
            $products = $this->product->with([
                'compareList' => function ($query) use ($user) {
                    return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                },
                'clearanceSale' => function ($query) {
                    return $query->active();
                }
            ])
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->active()
                ->inRandomOrder()
                ->paginate($limit, ['*'], 'page', $offset);
        }

        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products, true) : [];

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

//    public function getMostSearchingProductsList(Request $request): JsonResponse
//    {
//        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
//        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
//        return response()->json([
//            'total_size' => $products->total(),
//            'limit' => (int)$request['limit'],
//            'offset' => (int)$request['offset'],
//            'products' => $productsList
//        ]);
//    }

    public function getDigitalProductsAuthorList(Request $request): JsonResponse
    {
        $productIds = Product::active()
            ->when($request['seller_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['seller_id'] != 0, function ($query) use ($request) {
                return $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id']]);
            })->pluck('id')->toArray();
        $authors = ProductManager::getProductAuthorList(productIds: $productIds);
        return response()->json($authors->values());
    }

    public function getDigitalPublishingHouseList(Request $request): JsonResponse
    {
        $productIds = Product::active()
            ->when($request['seller_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['seller_id'] != 0, function ($query) use ($request) {
                return $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id']]);
            })->pluck('id')->toArray();
        $publishingHouseList = ProductManager::getPublishingHouseList(productIds: $productIds);
        return response()->json($publishingHouseList->values());
    }

    public function getClearanceSale(Request $request): JsonResponse
    {
        $productIds = StockClearanceProduct::active()->whereHas('setup', function ($query) {
            $addedBy = getWebConfig(name: 'stock_clearance_vendor_offer_in_homepage') ? ['admin', 'vendor'] : ['admin'];
            return $query->where('show_in_homepage', 1)->whereIn('setup_by', $addedBy);
        })->whereHas('product', function ($query) {
            return $query->active()->with(['reviews', 'rating', 'clearanceSale' => function ($query) {
                return $query->active();
            }])->withCount('reviews');
        })->pluck('product_id')->toArray();

        $basedQuery = Product::active()->whereIn('id', $productIds)->with(['reviews', 'rating', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->withCount('reviews');

        $products = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $basedQuery, dataLimit: (int)($request['limit'] ?? 10));

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)($request['limit'] ?? 10),
            'offset' => (int)($request['offset'] ?? 1),
            'products' => Helpers::product_data_formatting($products->items(), true)
        ]);
    }
}
