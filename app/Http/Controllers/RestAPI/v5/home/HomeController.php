<?php

namespace App\Http\Controllers\RestAPI\v5\home;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\BannerResource;
use App\Http\Resources\RestAPI\v5\CategoryResource;
use App\Http\Resources\RestAPI\v5\FeatureDealResource;
use App\Http\Resources\RestAPI\v5\OrderResource;
use App\Http\Resources\RestAPI\v5\ProductResource;
use App\Http\Resources\RestAPI\v5\PartnerResource;
use App\Models\Product;
use App\Models\Seller;
use App\Models\CartProduct;
use App\Models\Category;
use App\Models\SellerWallet;
use App\User;
use App\Utils\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Utils\ProductManager;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Http\Resources\RestAPI\v5\productArrivalResource;
use App\Http\Resources\RestAPI\v5\ProductDetailsResource;
use App\Http\Resources\RestAPI\v5\SettingResource;
use App\Models\OrderDetail;
use App\Models\RecentViewdProduct;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
//public function getHome(Request $request): JsonResponse
//{
//    try {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//            // $data['banner'] = BannerResource::collection(\App\Models\Banner::where('lang', $lang)->get());
//        }
//        $data['banner_image'] = asset('public/images/home/banner-image.jpg');
//        if (auth()->user()){
//            $user=auth()->user()->id;
//            $data['cart_count']=CartProduct::where('customer_id',$user)->count();
//        }else{
//            $data['cart_count']=0;
//        }
////        // categories
//        $query = Category::where('lang', $lang);
//        $query=$query->where('home_status',1);
//        $categories = $query->get();
//
//////            dd($categories);
//        $parentCategories = $categories->where('parent_id', 0);
//        $categories_returned = CategoryResource::collection($parentCategories);
////
////        return $this->responseMsg(
////            'Data has been returned successfully',
////            $categories_returned,
////            200
//        $data['categories'] = CategoryResource::collection($categories_returned);
//
////        $data['new_arrival_products'] = ProductArrivalResource::collection(
////            Product::select('products.*')
////                ->where('slug', '!=', null)
////                ->where('products.status', 1)
////                ->where('lang',$lang)
////                ->where('products.current_stock', '>=', 1)
////                ->where('products.status', 1)
////                ->orderBy('products.created_at', 'desc')
////                ->take(10)
////                ->get()
////        );
//
//
//
//        $products = Product::select('products.*')
//            ->whereNotNull('slug')
//            ->where('products.status', 1)
//            ->where('products.current_stock', '>=', 1)
//            ->orderBy('products.created_at', 'desc')
//            ->take(10)
//            ->get();
//
//// Pass lang to each resource
//        $data['new_arrival_products'] = $products->map(function ($product) use ($lang) {
//            return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
//        });
//
//
//
//
//
//
//        // partners
//        $data['partners'] = PartnerResource::collection(\App\Models\Partner::all());
//
//
//
//        try {
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
////            $bestSellingProducts=Product::where('lang',$lang)->where('status',1)->where('current_stock','>=',1)->orderBy('total_sales','desc')->limit(10)->get();
//////            dd($bestSellingProducts);
////            $data['best_sellers'] = ProductArrivalResource::collection($bestSellingProducts);
//
//
//            $bestSellingProducts = Product::where('status', 1)
//                ->where('current_stock', '>=', 1)
//                ->orderBy('total_sales', 'desc')
//                ->limit(10)
//                ->get();
//
//            $data['best_sellers'] = $bestSellingProducts->map(function ($product) use ($lang) {
//                return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
//            });
//
//        } catch (\Exception $e) {
//            $data['best_sellers']=[];
//        }
//
////        $data['best_sellers']=[];
//
//        // offers
//        $lang = $request->header('Accept-Language', 'en');
//        $data['offers'] = FeatureDealResource::collection(
//            \App\Models\FeatureDeal::where("status", 1)->get()
//        )->additional(['lang' => $lang]);
//
//        return $this->responseMsg("the data has been returned successfully", $data, 200);
//    } catch (\Exception $e) {
//        return $this->responseMsg("An error occurred: " . $e->getMessage(), null, 500);
//    }
//}


    public function getHome(Request $request): JsonResponse
    {
        try {
            $lang = $request->header('Accept-Language', 'en');
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';

            // Banner data
            $data['banner_image'] = asset('public/images/home/banner-image.jpg');

            // Cart count
            if (auth()->user()) {
                $user = auth()->user()->id;
                $data['cart_count'] = CartProduct::where('customer_id', $user)->count();
            } else {
                $data['cart_count'] = 0;
            }

            // Categories - only in requested language
            $categories = Category::where('lang', $lang)
                ->where('home_status', 1)
                ->get();

            $parentCategories = $categories->where('parent_id', 0);
            $data['categories'] = CategoryResource::collection($parentCategories);

            // New Arrival Products - ONLY in requested language
            $newArrivalProducts = Product::where('lang', $lang) // Critical: filter by language
            ->whereNotNull('slug')
                ->where('status', 1)
                ->where('current_stock', '>=', 1)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $data['new_arrival_products'] = $newArrivalProducts->map(function ($product) use ($lang) {
                return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
            });

            // Partners
            $data['partners'] = PartnerResource::collection(\App\Models\Partner::all());

            // Best Selling Products - ONLY in requested language
            try {
                $bestSellingProducts = Product::where('lang', $lang) // Critical: filter by language
                ->where('status', 1)
                    ->where('current_stock', '>=', 1)
                    ->orderBy('total_sales', 'desc')
                    ->limit(10)
                    ->get();

                $data['best_sellers'] = $bestSellingProducts->map(function ($product) use ($lang) {
                    return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
                });
            } catch (\Exception $e) {
                $data['best_sellers'] = [];
            }

            // Offers
            $data['offers'] = FeatureDealResource::collection(
                \App\Models\FeatureDeal::where("status", 1)->get()
            )->additional(['lang' => $lang]);

            return $this->responseMsg("the data has been returned successfully", $data, 200);
        } catch (\Exception $e) {
            return $this->responseMsg("An error occurred: " . $e->getMessage(), null, 500);
        }
    }






    public function getSettings(){
        try {
            $data = [];
            foreach (\App\Models\BusinessSetting::get() as $setting) {
                $data[$setting->type] = $setting->value;
            }
            return $this->responseMsg("the data has been returned successfully", $data, 200);
        } catch (\Exception $e) {
            return $this->responseMsg("An error occurred: " . $e->getMessage(), null, 500);
        }
    }
    public function getOffers(Request $request): JsonResponse
    {
       $offers_per_page = $request->input('offers_per_page', null);
        $page_number = $request->input('page_number', 1);

        if ($offers_per_page == null) {
            $offers = \App\Models\FeatureDeal::where("status", 1)->get();
            return $this->responseMsg('offers has been returned successfully', [
                'pagination' => null,
                'offers_per_page' => null,
                'offers' => FeatureDealResource::collection($offers),
            ], 200);
        } else {
            $paginatedOffers = \App\Models\FeatureDeal::where("status", 1)->paginate($offers_per_page, ['*'], 'page', $page_number);
            return $this->responseMsg('offers has been returned successfully', [
                'pagination' => [
                    'total' => $paginatedOffers->total(),
                    'per_page' => $paginatedOffers->perPage(),
                    'current_page' => $paginatedOffers->currentPage(),
                    'next_page_url' => $paginatedOffers->nextPageUrl(),
                    'prev_page_url' => $paginatedOffers->previousPageUrl(),
                    'from' => $paginatedOffers->firstItem(),
                    'to' => $paginatedOffers->lastItem(),
                ],
                'offers_per_page' => $offers_per_page,
                'offers' => FeatureDealResource::collection($paginatedOffers),
            ], 200);
        }
    }
    public function getPartners(Request $request): JsonResponse
    {
       $partners_per_page = $request->input('partners_per_page', null); // Note: Fix typo in variable name if needed
       $page_number = $request->input('page_number', 1);

        if ($partners_per_page == null) {
            $partners = PartnerResource::collection(\App\Models\Partner::all());
            return $this->responseMsg('Partners fetched successfully', [
                'pagination' => null,
                'partners_per_page' => null,
                'partners' => $partners,
            ], 200);
        } else {
            $partners = PartnerResource::collection(\App\Models\Partner::paginate($partners_per_page, ['*'], 'page', $page_number));

            return $this->responseMsg('Partners fetched successfully', [
                'pagination' => [
                    'total' => $partners->total(),
                    'per_page' => $partners->perPage(),
                    'current_page' => $partners->currentPage(),
                    'next_page_url' => $partners->nextPageUrl(),
                    'prev_page_url' => $partners->previousPageUrl(),
                    'from' => $partners->firstItem(),
                    'to' => $partners->lastItem(),
                ],
                'partners_per_page' => $partners_per_page,
                'partners' => $partners,
            ], 200);
        }
    }

public function successResponse($data = null): JsonResponse
{
    return response()->json([
        'data' => $data,
        'msg' => 'success fetching data',
        'status' => 200
    ]);
}

/**
 * @return JsonResponse
 */
public function errorResponse($data = null): JsonResponse
{
    return response()->json([
        'data' => $data,
        'msg' => 'error fetching data',
        'status' => 500
    ]);
}

    public function responseMsg($msg, $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }

    public function getProductDetails(Request $request)
    {
//        if (!$id) {
//            return $this->errorResponse('Product ID or slug is required');
//        }





//        $slug=$request->slug;
//        dd($slug);
//
////        $product = Product::where('wordpress_id', $id)->orWhere('slug', $id)->first();
//        $product = Product::where('slug',$slug)->first();
//        dd($product);
//        if (!$product) {
//            if (!Product::where('slug', $slug)->first()) {
//                return $this->responseMsg('Product not found',null,404);
//            }else {
//                $product = Product::where('slug', $slug)->first();
//            }
//        }


        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        }

        if (!$request->has('slug')){
            $slug = $request->slug;
            $product = Product::where('slug', $slug)->where('lang',$lang)->first();
        }else{
            $slug = $request->slug;


//            $slug = urlencode($request->slug);
            $product = Product::where('slug', $slug)->where('lang',$lang)->first();

        }

        if (!$product) {
            return $this->responseMsg('Product not found', null, 404);
        }








        // Store the recent view
//        if (auth()->check()) {
//            $this->storeRecentView($id);
//        }

//        $orderIds = OrderDetail::where('product_id', $id)
////            ->pluck('order_id');
////
////        if (!$orderIds->count()) {
////            $orderIds = OrderDetail::inRandomOrder()->take(4)->pluck('order_id');
////        }

//        $relatedProductIds = OrderDetail::whereIn('order_id', $orderIds)
//            ->where('product_id', '!=', $id)
//            ->pluck('product_id')
//            ->unique()
//            ->take(4);
//        $product = Product::find($id);
//        $relatedProducts = Product::when($product->product_ids, function ($query) use ($product) {
//            return $query->whereIn('id', json_decode($product->product_ids) ?? []);
//        })->get();
//        $data["productDetails"] = new ProductDetailsResource($product);

        $data["productDetails"] = ProductDetailsResource::make($product)->additional(['lang' => $lang]);



//        $data["related_products"] = json_decode($product->related_ids) ?? [];
//        $data['buy_it_together']=[];

        return response()->json([
            'data' => $data,
            'msg' => 'Product details retrieved successfully',
            'status' => 200
        ] , 200);
    }


    public function storeRecentView($productId)
    {

        $customerId =  auth()->user()->id;
        // dd( $customerId);

        RecentViewdProduct::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->delete();

        RecentViewdProduct::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
        ]);

        $count = RecentViewdProduct::where('customer_id', $customerId)->count();

        if ($count > 10) {
            $excess = RecentViewdProduct::where('customer_id', $customerId)
                ->orderBy('created_at')
                ->take($count - 10)
                ->get();

            foreach ($excess as $item) {
                $item->delete();
            }
        }
    }

}
