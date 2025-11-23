<?php

namespace App\Http\Controllers\RestAPI\v5\user\profile;

use App\Enums\ViewPaths\Admin\Notification;
use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\UserResource;
use App\Http\Resources\RestAPI\v5\CartResource;
use App\Http\Resources\RestAPI\v5\productArrivalResource;
use App\Models\Contact;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Wishlist;
use App\Utils\Helpers;
use App\Http\Resources\RestAPI\v5\NotificationResource;
use App\Http\Resources\RestAPI\v5\CartProductResource;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;


class UserController extends Controller
{

    public function getData(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }
        try {
            return $this->responseMsg(
                'the data has been returned successfully',
                  UserResource::make($user),
                200
            );
        } catch (\Exception $e) {
            return $this->responseMsg('failed to get user data!', null, 500);
        }
    }
//    public function getCart(Request $request)
//    {
//
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        }
//
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//        $cartProducts = CartProduct::where('customer_id', $user->id)->get();
//
////        dd(CartProductResource::collection($cartProducts));
////dd($lang);
//        return response()->json(
//           [
//               'message'=>'the data has been returned successfully',
//               'data'=>CartProductResource::collection($cartProducts)->additional(['lang'=>$lang]),
//               'status'=>200
//           ]
//        );
//
//    }






//    public function getCart(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        } else {
//            $lang = 'en'; // Default language
//        }
//
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//
//        $cartProducts = CartProduct::where('customer_id', $user->id)->get();
//
//        // Map each cart product and pass lang individually
//        $cartProductsWithLang = $cartProducts->map(function ($cartProduct) use ($lang) {
//            return CartProductResource::make($cartProduct)->additional(['lang' => $lang]);
//        });
//
//        return response()->json([
//            'message' => 'the data has been returned successfully',
//            'data' => $cartProductsWithLang->values(),
//            'status' => 200
//        ]);
//    }



    public function getCart(Request $request)
    {
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        } else {
            $lang = 'en';
        }

        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }

        $cartProducts = CartProduct::where('customer_id', $user->id)->get();

        // ✅ ENHANCED: Remove duplicates and merge quantities
        $uniqueCartItems = $this->deduplicateCartItems($cartProducts);

        $cartProductsWithLang = $uniqueCartItems->map(function ($cartProduct) use ($lang) {
            return CartProductResource::make($cartProduct)->additional(['lang' => $lang]);
        });
//dd($cartProductsWithLang);
        return response()->json([
            'message' => 'the data has been returned successfully',
            'data' => $cartProductsWithLang->values(),
            'status' => 200
        ]);
    }

    /**
     * ✅ Remove duplicate cart items and merge quantities
     */
    private function deduplicateCartItems($cartProducts)
    {
        $groupedItems = [];
        $itemsToDelete = [];

        foreach ($cartProducts as $cartProduct) {
            // Create a unique key based on product, variant, and buy_together combination
            $key = $cartProduct->product_id . '_' .
                ($cartProduct->variant_id ?? 'no_variant') . '_' .
                ($cartProduct->buy_together_id ?? 'no_bundle');

            // For buy-together items, also consider the selected_buy_together_ids
            if ($cartProduct->buy_together_id) {
                $buyTogetherIds = $this->parseBuyTogetherIds($cartProduct->selected_buy_together_ids);
                sort($buyTogetherIds);
                $key .= '_' . md5(json_encode($buyTogetherIds));
            }

            if (!isset($groupedItems[$key])) {
                $groupedItems[$key] = $cartProduct;
            } else {
                // Merge quantities and mark duplicate for deletion
                $groupedItems[$key]->quantity += $cartProduct->quantity;
                $itemsToDelete[] = $cartProduct->id;
            }
        }

        // Delete duplicates
        if (!empty($itemsToDelete)) {
            CartProduct::whereIn('id', $itemsToDelete)->delete();
        }

        return collect(array_values($groupedItems));
    }











//    public function getCart(Request $request)
//    {
//        $lang = $request->header('Accept-Language');
//        if ($lang) {
//            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
//        } else {
//            $lang = 'en'; // Default language
//        }
//
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//
//        $cartProducts = CartProduct::where('customer_id', $user->id)->get();
//        $filteredCartProducts = collect();
//
//        foreach ($cartProducts as $cartProduct) {
////            $product = $cartProduct->product;
//            $product=Product::where('wordpress_id', $cartProduct->product_id)->first();
//
//
//            if (!$product) {
//                continue; // Skip if product doesn't exist
//            }
//
//            // Check if the product matches the requested language
//            if ($product->lang == $lang) {
//                $filteredCartProducts->push($cartProduct);
//            } else {
//                // Product doesn't match requested language, look for translation
//                $translationField = "translation_{$lang}";
//                $translationId = $product->$translationField ?? null;
//
//                if ($translationId) {
//                    // Translation exists, check if cart already has this translation
//                    $translatedProductExists = $filteredCartProducts->contains(function ($item) use ($translationId) {
//                        return $item->product_id == $translationId;
//                    });
//
//                    if (!$translatedProductExists) {
//                        // Check if translated product exists in database
//                        $translatedProduct = Product::find($translationId);
//
//                        if ($translatedProduct) {
//                            // Create a temporary cart product instance with translated product
//                            $translatedCartProduct = clone $cartProduct;
//                            $translatedCartProduct->product_id = $translationId;
//                            $translatedCartProduct->setRelation('product', $translatedProduct);
//                            $filteredCartProducts->push($translatedCartProduct);
//                        } else {
//                            // Translation doesn't exist in database, use original
//                            $filteredCartProducts->push($cartProduct);
//                        }
//                    } else {
//                        // Translation already in cart, merge quantities
//                        $existingIndex = $filteredCartProducts->search(function ($item) use ($translationId) {
//                            return $item->product_id == $translationId;
//                        });
//
//                        if ($existingIndex !== false) {
//                            $existingItem = $filteredCartProducts[$existingIndex];
//                            $existingItem->quantity += $cartProduct->quantity;
//                        }
//                    }
//                } else {
//                    // No translation available, use original product
//                    $filteredCartProducts->push($cartProduct);
//                }
//            }
//        }
//
//        return response()->json([
//            'message' => 'the data has been returned successfully',
//            'data' => CartProductResource::collection($filteredCartProducts),
//            'status' => 200
//        ]);
//    }






//    public function addToCart(Request $request)
//    {
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//
//        $validator = Validator::make($request->all(), [
//            'product_id' => 'required|integer',
//            'quantity' => 'required|integer',
//            'variant_id' => 'nullable|integer',
//            'buy_together_id'=>'nullable|exists:buy_it_togethers,wordpress_id',
//            'selected_buy_together_ids' => 'nullable|array',
//            'selected_buy_together_ids.*' => 'integer',
//        ]);
//        if ($validator->fails()) {
//            return response()->json([
//                "msg" => 'Validation failed',
//                'errors' => $validator->errors(),
//                'status' => 422
//            ], 422);
//        }
//
//        $productId = $request->product_id;
//        $variantId = $request->variant_id;
//        $buyTogetherId = $request->buy_together_id;
//        $buyTogetherIds = $request->selected_buy_together_ids ?? [];
//
//
//
//        $query = CartProduct::where('customer_id', $user->id)
//            ->where('product_id', $productId);
//
//        if ($variantId) {
//            $query->where('variant_id', $variantId);
//        }
//        if ($buyTogetherId) {
//            $query->where('buy_together_id', $buyTogetherId);
//        }
//
//        // If buy_together_ids exist, match exactly (convert array to JSON if stored as such)
//        if (!empty($buyTogetherIds)) {
//            $query->whereJsonContains('selected_buy_together_ids', $buyTogetherIds);
//        }
//
////        dd($query->first(),$request->quantity);
//
//        if ($query->exists()) {
//            if ($query->first()->quantity+$request->quantity<=0){
//                $deleted = $query->delete();
//
//                if ($deleted) {
//                    return $this->responseMsg('Product removed from cart successfully.', null, 200);
//                } else {
//                    return $this->responseMsg('No matching product found in cart.', null, 404);
//                }
//            }else{
//                $query->first()->update([
//                    'quantity'=>$query->first()->quantity+$request->quantity
//                ]);
//                return $this->responseMsg('Product has been added to cart successfully.', null, 200);
//
//            }
//
//        }
//
//        else {
//
//            $buyTogetherIds = $request->has('selected_buy_together_ids')
//                ? json_encode($request->selected_buy_together_ids)
//                : null;
//
//            // Check if product already exists in cart
////            $product = CartProduct::where('product_id', $request->product_id)
////                ->where('customer_id', $user->id)
////                ->where('variant_id', $request->variant_id)
////                ->first();
//
////            if ($product) {
////                $product->update([
////                    'quantity' => $product->quantity + $request->quantity,
////                    'buy_together_ids' => $buyTogetherIds,
////                ]);
////
////                return $this->responseMsg(
////                    'Product added to cart successfully',
////                    null,
////                    200
////                );
////            }
//
//            // Create new cart item
//            CartProduct::create([
//                'product_id' => $request->product_id,
//                'customer_id' => $user->id,
//                'quantity' => $request->quantity,
//                'variant_id' => $request->variant_id,
//                'selected_buy_together_ids' => $buyTogetherIds,
//                'buy_together_id' => $request->buy_together_id,
//            ]);
//
//            return $this->responseMsg(
//                'Product added to cart successfully',
//                null,
//                200
//            );
//        }
//    }






//    public function addToCart(Request $request)
//    {
//
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//
//        $validator = Validator::make($request->all(), [
//            'product_id' => 'required|integer',
//            'quantity' => 'required|integer',
//            'variant_id' => 'nullable|integer',
//            'buy_together_id' => 'nullable|exists:buy_it_togethers,wordpress_id',
//            'selected_buy_together_ids' => 'nullable|array',
//            'selected_buy_together_ids.*' => 'integer',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json([
//                "msg" => 'Validation failed',
//                'errors' => $validator->errors(),
//                'status' => 422
//            ], 422);
//        }
//
//        $productId = $request->product_id;
//        $variantId = $request->variant_id;
//        $buyTogetherId = $request->buy_together_id;
////        $selectedBuyTogetherIds = $request->selected_buy_together_ids ?? [];
////    //        $product=Product::where('wordpress_id',$productId)->orWhere('translation_ar',$product->wordpress_id)->orWhere('translation_en',$product->wordpress_id)->first();
////        // Sort the array to ensure consistent comparison
////        sort($selectedBuyTogetherIds);
//
//
//
//
//
//
//        $selectedBuyTogetherIds = $request->selected_buy_together_ids ?? [];
//        $expandedBuyTogetherIds = [];
//
//        if (!empty($selectedBuyTogetherIds)) {
//            // Get all products that match any of the IDs (as main ID or translation)
//            $products = Product::where(function($query) use ($selectedBuyTogetherIds) {
//                $query->whereIn('wordpress_id', $selectedBuyTogetherIds)
//                    ->orWhereIn('translation_ar', $selectedBuyTogetherIds)
//                    ->orWhereIn('translation_en', $selectedBuyTogetherIds);
//            })->get();
//
//            // Collect all IDs including translations
//            $expandedBuyTogetherIds = $products->flatMap(function ($product) {
//                return collect([
//                    $product->wordpress_id,
//                    $product->translation_ar,
//                    $product->translation_en,
//                ])->filter();
//            })->unique()->sort()->values()->toArray();
//
//            // If no products found, keep the original IDs
//            if (empty($expandedBuyTogetherIds)) {
//                $expandedBuyTogetherIds = $selectedBuyTogetherIds;
//            }
//        }
//
//// Use the expanded IDs or original if expansion failed
//        $selectedBuyTogetherIds = !empty($expandedBuyTogetherIds)
//            ? $expandedBuyTogetherIds
//            : $selectedBuyTogetherIds;
//
//// Sort the final array
//        sort($selectedBuyTogetherIds);
//
//
//
//
//
//        // Build base query
////        $cartProduct=Product::where('wordpress_id',$productId)->orWhere('translation_ar',$productId)->orWhere('translation_en',$productId)->first();
////        $query = CartProduct::where('customer_id', $user->id)
//////            ->where('product_id', $productId)
////            ->whereIn('product_id', $cartProduct)
//////            ->orderBy('product_id', $productId->)
////        ;
//
//
//        $cartProduct = Product::where('wordpress_id', $productId)
//            ->orWhere('translation_ar', $productId)
//            ->orWhere('translation_en', $productId)
//            ->first();
//
//        if (!$cartProduct) {
//            return $this->responseMsg('Product not found.', null, 404);
//        }
//
//// Build array of all related product IDs
//        $productIds = [$cartProduct->wordpress_id];
//
//        if ($cartProduct->translation_ar) {
//            $productIds[] = $cartProduct->translation_ar;
//        }
//
//        if ($cartProduct->translation_en) {
//            $productIds[] = $cartProduct->translation_en;
//        }
//
//// Remove duplicates
//        $productIds = array_unique($productIds);
//
//        $query = CartProduct::where('customer_id', $user->id)
//            ->whereIn('product_id', $productIds);
//
//
//
//
//
//
//        // Add variant condition
//        if ($variantId) {
//            $query->where('variant_id', $variantId);
//        } else {
//            $query->whereNull('variant_id');
//        }
//
//        // Add buy_together_id condition
//        if ($buyTogetherId) {
//            $query->where('buy_together_id', $buyTogetherId);
//        } else {
//            $query->whereNull('buy_together_id');
//        }
//
//        // Get all potential matching items
//        $cartItems = $query->get();
//
//        // Find exact match by comparing selected_buy_together_ids
//        $existingCartItem = null;
//        foreach ($cartItems as $item) {
//            $itemBuyTogetherIds = [];
//
//            if ($item->selected_buy_together_ids) {
//                // Decode if stored as JSON string
//                $decoded = is_string($item->selected_buy_together_ids)
//                    ? json_decode($item->selected_buy_together_ids, true)
//                    : $item->selected_buy_together_ids;
//
//                $itemBuyTogetherIds = is_array($decoded) ? $decoded : [];
//            }
//
//            // Sort for consistent comparison
//            sort($itemBuyTogetherIds);
//
//            // Check if arrays are exactly the same
//            if ($itemBuyTogetherIds === $selectedBuyTogetherIds) {
//                $existingCartItem = $item;
//                break;
//            }
//        }
//
//        // If exact match found, update quantity
//        if ($existingCartItem) {
//            $newQuantity = $existingCartItem->quantity + $request->quantity;
//
//            // If quantity becomes zero or negative, remove item
//            if ($newQuantity <= 0) {
//                $existingCartItem->delete();
//                return $this->responseMsg('Product removed from cart successfully.', null, 200);
//            }
//
//            // Update quantity
//            $existingCartItem->update([
//                'quantity' => $newQuantity
//            ]);
//
//            return $this->responseMsg('Product quantity updated in cart successfully.', null, 200);
//        }
//
//        // No exact match found, create new cart item
//        // Only create if quantity is positive
//        if ($request->quantity <= 0) {
//            return $this->responseMsg('Cannot add product with zero or negative quantity.', null, 400);
//        }
//
//        $buyTogetherIdsJson = !empty($selectedBuyTogetherIds)
//            ? json_encode($selectedBuyTogetherIds)
//            : null;
////dd($buyTogetherId,$buyTogetherIdsJson);
//        CartProduct::create([
//            'product_id' => $productId,
//            'customer_id' => $user->id,
//            'quantity' => $request->quantity,
//            'variant_id' => $variantId,
//            'selected_buy_together_ids' => $buyTogetherIdsJson,
//            'buy_together_id' => $buyTogetherId,
//        ]);
//
//        return $this->responseMsg('Product added to cart successfully.', null, 200);
//    }


    public function addToCart(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'buy_together_id' => 'nullable|exists:buy_it_togethers,wordpress_id',
            'selected_buy_together_ids' => 'nullable|array',
            'selected_buy_together_ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "msg" => 'Validation failed',
                'errors' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $buyTogetherId = $request->buy_together_id;
        $selectedBuyTogetherIds = $request->selected_buy_together_ids ?? [];

        // ✅ FIX: Get the canonical product ID (always use the main product ID, not translations)
        $canonicalProduct = $this->getCanonicalProduct($productId);

        if (!$canonicalProduct) {
            return $this->responseMsg('Product not found.', null, 404);
        }

        // ✅ FIX: Always use the canonical product ID to prevent duplicates
        $canonicalProductId = $canonicalProduct->wordpress_id;

        // ✅ FIX: Normalize selected_buy_together_ids to use canonical IDs
        $normalizedBuyTogetherIds = $this->normalizeBuyTogetherIds($selectedBuyTogetherIds);
        sort($normalizedBuyTogetherIds);

        // ✅ FIX: Simplified query - only check by canonical product ID
        $query = CartProduct::where('customer_id', $user->id)
            ->where('product_id', $canonicalProductId); // Use canonical ID only

        // Add variant condition
        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        // Add buy_together_id condition
        if ($buyTogetherId) {
            $query->where('buy_together_id', $buyTogetherId);
        } else {
            $query->whereNull('buy_together_id');
        }

        // ✅ FIX: Check for exact match including selected_buy_together_ids
        $existingCartItem = $query->get()->first(function ($item) use ($normalizedBuyTogetherIds) {
            $itemBuyTogetherIds = $this->parseBuyTogetherIds($item->selected_buy_together_ids);
            return $itemBuyTogetherIds === $normalizedBuyTogetherIds;
        });

        // If exact match found, update quantity
        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $request->quantity;

            // If quantity becomes zero or negative, remove item
            if ($newQuantity <= 0) {
                $existingCartItem->delete();
                return $this->responseMsg('Product removed from cart successfully.', null, 200);
            }

            // Update quantity
            $existingCartItem->update([
                'quantity' => $newQuantity
            ]);

            return $this->responseMsg('Product quantity updated in cart successfully.', null, 200);
        }

        // No exact match found, create new cart item
        // Only create if quantity is positive
        if ($request->quantity <= 0) {
            return $this->responseMsg('Cannot add product with zero or negative quantity.', null, 400);
        }

        $buyTogetherIdsJson = !empty($normalizedBuyTogetherIds)
            ? json_encode($normalizedBuyTogetherIds)
            : null;

        CartProduct::create([
            'product_id' => $canonicalProductId, // ✅ Use canonical ID
            'customer_id' => $user->id,
            'quantity' => $request->quantity,
            'variant_id' => $variantId,
            'selected_buy_together_ids' => $buyTogetherIdsJson,
            'buy_together_id' => $buyTogetherId,
        ]);

        return $this->responseMsg('Product added to cart successfully.', null, 200);
    }

    /**
     * ✅ Get the canonical product ID (main product, not translation)
     */
    private function getCanonicalProduct($productId)
    {
        // First, try to find the product directly
        $product = Product::where('wordpress_id', $productId)->first();

        if ($product) {
            // If this product is a translation, find its main product
            if ($product->translation_ar || $product->translation_en) {
                // This product has translations, so it might be a main product
                return $product;
            } else {
                // This product doesn't have translations, check if it's a translation of another product
                $mainProduct = Product::where('translation_ar', $productId)
                    ->orWhere('translation_en', $productId)
                    ->first();

                return $mainProduct ?: $product;
            }
        }

        // If product not found by wordpress_id, check if it's a translation ID
        $mainProduct = Product::where('translation_ar', $productId)
            ->orWhere('translation_en', $productId)
            ->first();

        return $mainProduct;
    }

    /**
     * ✅ Normalize buy together IDs to use canonical product IDs
     */
    private function normalizeBuyTogetherIds($buyTogetherIds)
    {
        if (empty($buyTogetherIds)) {
            return [];
        }

        $normalizedIds = [];

        foreach ($buyTogetherIds as $id) {
            $canonicalProduct = $this->getCanonicalProduct($id);
            if ($canonicalProduct) {
                $normalizedIds[] = $canonicalProduct->wordpress_id;
            }
        }

        return array_unique($normalizedIds);
    }

    /**
     * ✅ Parse buy together IDs from JSON string
     */
    private function parseBuyTogetherIds($buyTogetherIdsJson)
    {
        if (!$buyTogetherIdsJson) {
            return [];
        }

        $ids = is_string($buyTogetherIdsJson)
            ? json_decode($buyTogetherIdsJson, true)
            : $buyTogetherIdsJson;

        return is_array($ids) ? $ids : [];
    }







    public function clearCart()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }

        $cartItems = CartProduct::where('customer_id', $user->id)->delete();

        if ($cartItems) {
            return $this->responseMsg('Cart cleared successfully.', null, 200);
        } else {
            return $this->responseMsg('No matching cart items found.', null, 404);
        }
    }





    public function getMyNotifications(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg("Unauthorized", null, 401);
        }

        $perPage = $request->input('per_page', 10); // Default 10 per page
        $page = $request->input('page', 1);

        // Get notifications for the authenticated user
        $query = $user->notifications();

        // Filter by reference type if provided
        if ($request->has('refrence_type')) {
            $query->where('refrence_type', $request->input('refrence_type'));
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Get paginated or all results
        $notifications = $perPage
            ? $query->paginate($perPage, ['*'], 'page', $page)
            : $query->orderBy('created_at', 'desc')->get();
        $notificationData=NotificationResource::collection( $notifications);

        $notificationIds = $notifications->pluck('id')->toArray();

        if (!empty($notificationIds)) {
            \App\Models\Notification::whereIn('id', $notificationIds)->update(['is_seen' => 1]);
        }

        return $this->responseMsg('Notifications retrieved successfully', [
            'pagination' => $perPage ? [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'next_page_url' => $notifications->nextPageUrl(),
                'prev_page_url' => $notifications->previousPageUrl(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ] : null,
            'notifications' => $notificationData,
        ], 200);
    }
//        public function getCart(Request $request)
//    {
//        $user = auth()->user();
//        if (!$user) {
//            return $this->responseMsg('User not found.', null, 404);
//        }
//        try {
//            return $this->responseMsg(
//                'the data has been returned successfully',
//                  CartResource::make($user),
//                200
//            );
//        } catch (\Exception $e) {
//            return $this->responseMsg('failed to get user data!', null, 500);
//        }
//    }
    public function getProfile(Request $request){

    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }
        if($request->phone && $user->phone){
            return $this->responseMsg("user phone can't be updated", null, 500);
        }
        try {
            $user = User::find($user->id);
            $user->f_name = $request->input('first_name', $user->f_name);
            $user->l_name = $request->input('last_name', $user->l_name);
            $user->image = $request->input('image', $user->image);
            if($request->phone){
                $user->phone = $request->input('phone', $user->phone);
//                dd($user->phone);
            }
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/user_images', $imageName);
                $user->image = 'user_images/' . $imageName;
            } else {
                $user->image = $request->input('image', $user->image);
            }

            $user->update([
                'f_name' => $user->f_name,
                'l_name' => $user->l_name,
                'image' => $user->image,
                'phone' => $user->phone??'',
            ]);
            return $this->responseMsg('User profile has been updated successfully.', UserResource::make($user), 200);
        } catch (\Exception $e) {
            return $this->responseMsg('Failed to update user profile!', null, 500);
        }
    }





/* <<<<<<<<<<<<<<  ✨ Windsurf Command ⭐ >>>>>>>>>>>>>>>> */
    /**
     * Delete a user from both WordPress and locally.
     *
     * This endpoint expects the user to be authenticated.
     *
     * @param Request $request
     * @return JsonResponse
     */

/* <<<<<<<<<<  f183932e-cc10-4b7e-85a8-25c312c7c9bf  >>>>>>>>>>> */
//    public function deleteAccount(Request $request)
//
//
//    {
//        $user = auth()->user();
//
//        if (!$user) {
//            return response()->json(['message' => 'User not authenticated'], 401);
//        }
//
//        $user = \App\Models\User::find($user->id);
//
//
////        $user = auth()->user();
//        $email = $user->email;
//        $password = $user->password; // to authenticate in WP
//
//        // Step 1: Get JWT from WordPress
//        $loginResponse = Http::asForm()->post('https://maximfood.com/?rest_route=/simple-jwt-login/v1/auth', [
//            'email' => $email,
//            'password' => $password,
//        ]);
//        dd(
//            $email,
//            $password,
//            $loginResponse->status(),
//            $loginResponse->body(),
//            $loginResponse->json(),
//        );
//
//
//
//        if (!$loginResponse->ok() || !$loginResponse['success']) {
//            return response()->json(['message' => 'Failed to authenticate with WordPress'], 400);
//        }
//
//        $jwt = $loginResponse['data']['jwt'];
//
//        // Step 2: Delete from WordPress
//        $deleteResponse = Http::asForm()->delete('https://maximfood.com/?rest_route=/simple-jwt-login/v1/users', [
//            'JWT' => $jwt,
//        ]);
//
//        if (!$deleteResponse->ok() || !$deleteResponse['success']) {
//            return response()->json([
//                'message' => 'Failed to delete user from WordPress',
//                'response' => $deleteResponse->json(),
//            ], 400);
//        }
//
//        // Step 3: Delete locally
//        User::where('email', $email)->delete();
//
//        return response()->json([
//            'message' => 'User deleted successfully from both systems',
//        ]);
//
//
//
////        $user = auth()->user();
////        if (!$user) {
////            return $this->responseMsg('User not found.', null, 404);
////        }
////        try {
////            $user->delete();
////            return $this->responseMsg('User has been deleted successfully.', null, 200);
////        } catch (\Exception $e) {
////            return $this->responseMsg('Failed to delete user!', null, 500);
////        }
//    }




//    public function deleteAccount(Request $request)
//    {
//        $authUser = auth()->user();
//
//        if (!$authUser) {
//            return response()->json(['message' => 'User not authenticated'], 401);
//        }
//
//        $user = User::find($authUser->id);
//
//        if (!$user) {
//            return response()->json(['message' => 'User not found'], 404);
//        }
//
//        $email = $user->email;
//        $password = null;
//
//        try {
//            $password = Crypt::decryptString($user->wp_password);
//        } catch (\Exception $e) {
//            return response()->json(['message' => 'Cannot decrypt stored password'], 500);
//        }
//
//        // Step 1: Authenticate with WordPress
//        $loginResponse = Http::asForm()->post('https://maximfood.com/?rest_route=/simple-jwt-login/v1/auth', [
//            'email' => $email,
//            'password' => $password, // now plain
//        ]);
//
//        \Log::info('WP Login Response:', [
//            'email' => $email,
//            'password' => $password,
//            'status' => $loginResponse->status(),
//            'body' => $loginResponse->body(),
//            'json' => $loginResponse->json(),
//        ]);
//
//        if (!$loginResponse->ok() || empty($loginResponse['success'])) {
//            return response()->json([
//                'message' => 'Failed to authenticate with WordPress',
//                'response' => $loginResponse->json(),
//            ], 400);
//        }
//
//        $jwt = $loginResponse['data']['jwt'] ?? null;
//
//        if (!$jwt) {
//            return response()->json(['message' => 'No JWT received from WordPress'], 400);
//        }
//
//        // Step 2: Delete from WordPress
//        $deleteResponse = Http::asForm()->delete('https://maximfood.com/?rest_route=/simple-jwt-login/v1/users', [
//            'JWT' => $jwt,
//        ]);
//
//        if (!$deleteResponse->ok() || empty($deleteResponse['success'])) {
//            return response()->json([
//                'message' => 'Failed to delete user from WordPress',
//                'response' => $deleteResponse->json(),
//            ], 400);
//        }
//
//        // Step 3: Delete locally
//        $user->delete();
//
//        return response()->json(['message' => 'User deleted successfully from both systems']);
//    }



    public function deleteAccount()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('User not found.', null, 404);
        }
        try {
            $user->delete();
            return $this->responseMsg('User has been deleted successfully.', null, 200);
        } catch (\Exception $e) {
            return $this->responseMsg('Failed to delete user!', null, 500);
        }
    }

    public function get_wishlist(Request $request): JsonResponse
    {
        $user = auth()->user();
        // dd($user);
        $lang = $request->header('Accept-Language');

        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        }

        if (!$user) {
            return $this->responseMsg("Unauthorized", null, 401);
        }

        // $wishlist = Wishlist::with(['wishlistProduct' => function($query) {
        //         $query->active();
        //     }])
        //     ->where('customer_id', $user->id)
        //     ->get();


        $productIds = Wishlist::where('customer_id', $user->id)
            ->pluck('product_id');
        $products=[];

        foreach ($productIds as $productId) {
            $products[] = Product::where('wordpress_id', $productId)->first();
        }

        return $this->responseMsg(
            "The wishlist has been returned successfully",
            collect($products)->map(function ($product) use ($lang) {
                return ProductArrivalResource::make($product)->additional(['lang' => $lang]);
            }),
            200
        );
    }






    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => 'success_fetching_data',
            'status' => 200
        ]);
    }
    public function errorResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => 'error fetching data',
            'status' => 500
        ]);
    }
    public function responseMsg($msg, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $msg,
            'status' => $status
        ]);
    }



    //     public function add_to_wishlist(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'product_id' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
    //     }

    //     $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

    //     if (empty($wishlist)) {
    //         $wishlist = new Wishlist;
    //         $wishlist->customer_id = $request->user()->id;
    //         $wishlist->product_id = $request->product_id;
    //         $wishlist->save();
    //         return $this->responseMsg('successfully added!', null, 200);
    //     }

    //     return $this->responseMsg('Already in your wishlist', null, 409);
    // }

    // public function remove_from_wishlist(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'product_id' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
    //     }

    //     $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();

    //     if (!empty($wishlist)) {
    //         Wishlist::where(['customer_id' => $request->user()->id, 'product_id' => $request->product_id])->delete();
    //         return $this->responseMsg('successfully removed!', null, 200);

    //     }
    //     return $this->responseMsg('No such data found!', null, 404);
    // }

    public function wish_list(Request $request)
    {

        $wishlist = Wishlist::whereHas('wishlistProduct', function ($q) {
            return $q;
        })->with(['productFullInfo' => function ($query) {
            return $query->with(['clearanceSale' => function ($query) {
                return $query->active();
            }]);
        }])->where('customer_id', $request->user()->id)->get();

        $wishlist->map(function ($data) {
            $data['productFullInfo'] = Helpers::product_data_formatting(json_decode($data['productFullInfo'], true));
            return $data;
        });

        return response()->json($wishlist, 200);
    }

     public function toggle_wishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,wordpress_id',
        ]);

        if ($validator->fails()) {
            return $this->responseMsg('an invalid product id provided', null, 403);
        }

        $wishlist = Wishlist::where('customer_id', $request->user()->id)->where('product_id', $request->product_id)->first();
        Log::info('before empty wishlist', ['product_id' => $request->product_id, 'user_id' => auth()->user()->id]);

        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            $wishlist->customer_id = auth()->user()->id;
            $wishlist->product_id = $request->product_id;
            $wishlist->save();
            // Product::where('id', $request->product_id)->update('is_wishlisted', true);
            Log::info('Product added to wishlist', ['product_id' => $request->product_id, 'user_id' => auth()->user()->id]);

            return $this->responseMsg('successfully added!', null, 200);
        }else{
            Wishlist::where(['customer_id' => auth()->user()->id, 'product_id' => $request->product_id])->delete();
        }

        return $this->responseMsg('successfully removed!', null, 200);
    }

    // public function storeMessage(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         "phone" => "required|numeric",
    //         'subject' => 'required|string',
    //         'message' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             "msg" => 'Validation failed',
    //             'errors' => $validator->errors(),
    //             'status' => 422
    //         ], 422);
    //     }

    //     try {
    //         $contact = Contact::create([
    //             'subject' => $request->subject,
    //             'message' => $request->message,
    //             'mobile_number' => $request->phone,
    //         ]);

    //         return response()->json([
    //             "msg" => 'Message sent successfully',
    //             'data' => null,
    //             'status' => 200
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "msg" => 'Failed to send message',
    //             'error' => $e->getMessage(),
    //             'status' => 500
    //         ], 500);
    //     }
    // }



}




