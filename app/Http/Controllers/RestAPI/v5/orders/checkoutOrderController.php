<?php

namespace App\Http\Controllers\RestAPI\v5\orders;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\OrderResource;
use App\Http\Resources\RestAPI\v5\OrderDetailsResource;
use App\Http\Resources\RestAPI\v5\ReOrderDetailsResource;
use App\Http\Resources\RestAPI\v5\OfflinePaymentMethodResource;
use App\Http\Resources\RestAPI\v5\productArrivalResource;
use App\Http\Resources\RestAPI\v5\ProductResource;

// use App\Traits\PayMobTrait;
use App\Models\UserAddress;
use App\Models\CartProduct;
use App\Traits\PayMobTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\OfflinePaymentMethod;
use App\Models\Order;
use App\Models\Review;
use App\Models\OrderDetail;
use App\Models\PaymentHestory;
use App\Models\Product;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class checkoutOrderController extends Controller
{
    use PayMobTrait;

    public function responseMsg($msg, $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }


    public function getOrders(Request $request)
    {
        $orders_per_page = $request->input('orders_per_page', null);
        $page_number = $request->input('page_number', 1);

        $ordersQuery = Order::where('customer_id', auth()->user()->id)
            ->orderBy('id', 'desc');


        $status = $request->input('status');


        if ($request->has('status') && $request->status != 'all') {
            if ($request->status == 'confirmed') {
                $status = ['confirmed','preparing'];
                $ordersQuery = $ordersQuery->whereIn('order_status',$status);
            }
            $ordersQuery = $ordersQuery->where('order_status', $status);
        }


        if ($orders_per_page === null) {
            $orders = $ordersQuery->get();
            $pagination = null;
        } else {
            $orders = $ordersQuery->paginate($orders_per_page, ['*'], 'page', $page_number);
            $pagination = [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'next_page_url' => $orders->nextPageUrl(),
                'prev_page_url' => $orders->previousPageUrl(),
                'from' => optional($orders->first())->id,
                'to' => optional($orders->last())->id,
            ];
        }

        return $this->responseMsg('data has been returned successfully', [
            'pagination' => $pagination,
            'orders_per_page' => $orders_per_page,
            'orders' => OrderResource::collection($orders),
        ], 200);
    }




    public function checkout(Request $request)
    {
//        $proceed_without_minimum_amount_or_maximum_amount=0;
//        if (Coupon::where('code',$request->coupon_code)->exists() && Coupon::where('code',$request->coupon_code)->maximum_amount<=0) {
//            $proceed_without_minimum_amount_or_maximum_amount=1;
//        }
//        dd($request->all());
        // تحويل البيانات قبل الـ validation
//        $request->merge([
//            'product_quantities' => array_map('intval', $request->product_quantities ?? []),
//            'product_variations' => array_map(function ($val) {
//                return $val ? intval($val) : null;
//            }, $request->product_variations ?? []),
////            'buy_together_ids' => array_map(function ($val) {
////                return $val ? intval($val) : null;
////            }, $request->buy_together_ids ?? []),
////            'product_price' => array_map('floatval', $request->product_price ?? []),
////            'buy_together_price' => array_map('floatval', $request->buy_together_price ?? []),
//
//            'buy_together_ids' => array_map(function ($item) {
//                if (is_array($item)) {
//                    return array_map('intval', $item);
//                }
//                $decoded = json_decode($item, true);
//                return is_array($decoded) ? array_map('intval', $decoded) : [(int)$item];
//            }, $request->buy_together_ids ?? []),
//
//
//            'product_price'      => array_map('floatval', $request->product_price ?? []),
//
//            'buy_together_price' => array_map(function ($item) {
//                if (is_array($item)) {
//                    return array_map('floatval', $item);
//                }
//                // Try to decode if it's a string like '[365,675]'
//                $decoded = json_decode($item, true);
//                return is_array($decoded) ? array_map('floatval', $decoded) : [(float)$item];
//            }, $request->buy_together_price ?? []),
//        ]);
//        $productCount = count($request->input('product_ids', []));
//
//
//        $validator = Validator::make($request->all(), [
//            'product_ids' => 'required|array|exists:products,wordpress_id',
//            'product_ids.*' => 'required|exists:products,wordpress_id',
//            'product_quantities' => 'required|array|size:' . count($request->input('product_ids')),
//            'product_quantities.*' => 'nullable|numeric|min:1',
//            'product_variations' => 'required|array|size:' . count($request->input('product_ids')),
//            'product_variations.*' => 'nullable|numeric',
////                'buy_together_ids' => 'required|array|size:' . count($request->input('product_ids')),
////                'buy_together_ids.*' => 'nullable|numeric',
////                'product_price' => 'required|array|size:' . count($request->input('product_ids')),
////                'product_price.*' => 'nullable|numeric',
////
////
////                'buy_together_price' => 'required|array|size:' . count($request->input('product_ids', [])),
////                'buy_together_price.*' => 'required|array',
////                'buy_together_price.*.*' => 'nullable|numeric',
//
//
////            'buy_together_ids' => 'nullable|array|max:' . $productCount,
////            'buy_together_ids.*' => 'nullable|numeric',
//            'buy_together_ids' => 'nullable|array|max:' . $productCount,
//            'buy_together_ids.*' => 'array', // Changed from 'nullable|numeric'
//            'buy_together_ids.*.*' => 'nullable|numeric',
//
//            'selected_buy_together_ids' => 'nullable|array|max:' . $productCount,
//            'selected_buy_together_ids.*' => 'array',
//            'selected_buy_together_ids.*.*' => 'nullable|numeric',
//
//
//
//
//            'product_price' => 'required|array|max:' . $productCount,
//            'product_price.*' => 'nullable|numeric',
//
//            'buy_together_price' => 'nullable|array|max:' . $productCount,
//            'buy_together_price.*' => 'array',
//            'buy_together_price.*.*' => 'nullable|numeric',
//
//            'order_note' => 'nullable|string|max:255',
//            'coupon_code' => 'nullable|string|exists:coupons,code',
//            'payment_method' => 'required|string|in:card_on_delivery,wallet,cash_on_delivery,paymob,instapay',
//            'address_id' => 'required',
//            'shipping_fee' => 'required|min:0|numeric',
//            'products_total_price' => 'nullable|min:0|numeric',
//            'coupon_amount'=>'nullable|min:0|numeric'
//        ]);
//        dd($request->selected_buy_together_ids,$request->buy_together_price);


        $productCount = count($request->input('product_ids', []));

        $request->merge([
            'product_quantities' => array_map('intval', $request->product_quantities ?? []),
            'product_variations' => array_map(function ($val) {
                return $val ? intval($val) : null;
            }, $request->product_variations ?? []),

            // buy_together_ids is a single ID per product, not an array
            'buy_together_ids' => array_map(function ($val) {
                return $val ? intval($val) : null;
            }, $request->buy_together_ids ?? []),

            'product_price' => array_map('floatval', $request->product_price ?? []),

            'buy_together_price' => array_map(function ($item) {
                if (is_array($item)) {
                    return array_map('floatval', $item);
                }
                $decoded = json_decode($item, true);
                return is_array($decoded) ? array_map('floatval', $decoded) : [(float)$item];
            }, $request->buy_together_price ?? []),
        ]);

// REPLACE the validation rules for buy_together_ids with this:
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array|exists:products,wordpress_id',
            'product_ids.*' => 'required|exists:products,wordpress_id',
            'product_quantities' => 'required|array|size:' . count($request->input('product_ids')),
            'product_quantities.*' => 'nullable|numeric|min:1',
            'product_variations' => 'required|array|size:' . count($request->input('product_ids')),
            'product_variations.*' => 'nullable|numeric',

            // buy_together_ids: can be single value or nested array
            'buy_together_ids' => 'nullable|array|max:' . $productCount,
            'buy_together_ids.*' => 'nullable', // Can be numeric or array

            // selected_buy_together_ids: nested array (matches selected_buy_together_ids[0][0]: 11622)
            'selected_buy_together_ids' => 'nullable|array|max:' . $productCount,
            'selected_buy_together_ids.*' => 'nullable|array',
            'selected_buy_together_ids.*.*' => 'nullable|numeric',

            'product_price' => 'required|array|max:' . $productCount,
            'product_price.*' => 'nullable|numeric',

            // buy_together_price: nested array (matches buy_together_price[0][0]: 329)
            'buy_together_price' => 'nullable|array|max:' . $productCount,
            'buy_together_price.*' => 'nullable|array',
            'buy_together_price.*.*' => 'nullable|numeric',

            'order_note' => 'nullable|string|max:255',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'payment_method' => 'required|string|in:card_on_delivery,wallet,cash_on_delivery,paymob,instapay',
            'address_id' => 'required',
            'shipping_fee' => 'required|min:0|numeric',
            'products_total_price' => 'nullable|min:0|numeric',
            'coupon_amount'=>'nullable|min:0|numeric'
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(implode(' ', $validator->errors()->all()), null, 403);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->responseMsg('Unauthorized', null, 401);
        }

        // Get products in the correct order
        $products = Product::with('digitalVariation')
            ->whereIn('wordpress_id', $request->product_ids)
            ->orderByRaw(sprintf("FIELD(wordpress_id, %s)", implode(',', $request->product_ids)))
            ->get()
            ->keyBy('wordpress_id'); // Key by wordpress_id for proper mapping

        if ($products->isEmpty()) {
            return $this->responseMsg('The product list is empty or invalid.', null, 404);
        }

        DB::beginTransaction();

//        try {
//        $total = $this->calculateOrderTotal($products, $request->product_quantities, $request->coupon_code, $request);
//        if ($total==-1){
//            return $this->responseMsg('there was an error handlig this checkout please check your coupon then proceed', null, 403);
//        }
        $orderAddress = UserAddress::where('id', $request->address_id)->first();

        $order = Order::create([
            'customer_id' => $user->id,
//            'paid_amount' => $total,
            'paid_amount' => $request->products_total_price-$request->coupon_amount+$request->shipping_fee,
            'order_note' => $request->order_note,
            'order_status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_method_title' => $this->getPaymentMethodTitle($request->payment_method),
//            'shipping_cost' => $this->calculateShippingCost($products, $request->product_quantities),
            'shipping_cost' => $request->shipping_fee,
//            'shipping_cost' => $this->shippping_fee,
            'shipping_address_data' => 'building number : ' . @$orderAddress->building_numer . ' , flat number : ' . @$orderAddress->address,
            'shipping_city_id' => @$orderAddress->city_id ?? null,
            'shipping_postal_code' => @$orderAddress->postal_code ?? null,
            'shipping_state_id' => @$orderAddress->area_id ?? null,
            'products_total_price'=>@$request->products_total_price,
            'coupon_amount'=>@$request->coupon_amount
        ]);

        // Handle coupon usage properly
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)->first();
//            dd('welcome');
            if ($coupon) {
                $usedBy = json_decode($coupon->used_by, true) ?? [];
                $usedBy[] = $user['email'];

                $coupon->update([
                    'used_by' => json_encode($usedBy),
                    'usage_count' => DB::raw('usage_count + 1')
                ]);
            }
        }
//dd($request->selected_buy_together_ids,$request->buy_together_price);
        // Create order details - Fix the array processing
        $this->createOrderDetailsFixed(
            $order,
            $products,
            $request->product_ids,
            $request->product_quantities,
            $request->product_variations,
            $request->buy_together_ids,
            $request->product_price,
            $request->buy_together_price,
            $request->selected_buy_together_ids
        );

        // Apply coupon if exists
//        $total = $this->applyCoupon($request->coupon_code, $order, $total);

        // Handle payment
        if (in_array($request['payment_method'], ['wallet', 'paymob', 'instapay'])) {
            DB::commit();
            return $this->responseMsg('order has submitted successfully and the payment is pending at the moment', OrderResource::make($order), 200);
        }

        DB::commit();

        // Sync to WooCommerce with proper array mapping
        $this->syncOrderToWooCommerce(
            order: $order,
            products: collect($request->product_ids)->map(function ($productId, $index) use ($request) {
                return [
                    'product_id' => $productId,
                    'qty' => $request->product_quantities[$index] ?? 1,
                    'variation' => $request->product_variations[$index] ?? null,
                ];
            })->values()
        );

        return $this->responseMsg('Checkout completed successfully', [
            'payment_url' => $paymentResponse['payment_url'] ?? null,
            'order_id' => $paymentResponse['order_id'] ?? $order->id,
            'payment_method' => $request->payment_method,
        ]);

    }



//    public function getOrderDetails(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'id' => 'required|exists:orders,id',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->responseMsg(implode(' ', $validator->errors()->all()), null, 403);
//        }
//
//        $orderId = $request->input('id');
//        $orderQuery = Order::where('customer_id', auth()->user()->id);
//        if ($orderId) {
//            $orderQuery = $orderQuery->where('id', $orderId);
//        } else {
//            return $this->responseMsg('Order ID is required', null, 400);
//        }
//
//        $order = $orderQuery->where('id', $request->id)->first();
//        if (!$order) {
//            return $this->responseMsg('Order not found', null, 404);
//        }
//        $data['order'] = OrderResource::make($order);
//
//        $data['products'] = OrderDetailsResource::collection($order->orderDetails);
//        return $this->responseMsg('success fetching data', $data, 200);
//    }





    public function getOrderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(implode(' ', $validator->errors()->all()), null, 403);
        }

        // Get language from header
        $lang = $request->header('Accept-Language');
        if ($lang) {
            $lang = Str::startsWith($lang, 'ar') ? 'ar' : 'en';
        } else {
            $lang = 'en'; // Default language
        }

        $orderId = $request->input('id');
        $orderQuery = Order::where('customer_id', auth()->user()->id);

        if ($orderId) {
            $orderQuery = $orderQuery->where('id', $orderId);
        } else {
            return $this->responseMsg('Order ID is required', null, 400);
        }

        $order = $orderQuery->where('id', $request->id)->first();

        if (!$order) {
            return $this->responseMsg('Order not found', null, 404);
        }

        // Pass language to OrderResource
        $data['order'] = OrderResource::make($order)->additional(['lang' => $lang]);

        // Map each order detail product and pass lang individually
        $data['products'] = $order->orderDetails->map(function ($orderDetail) use ($lang) {
            return OrderDetailsResource::make($orderDetail)->additional(['lang' => $lang]);
        })->values();

        return $this->responseMsg('success fetching data', $data, 200);
    }









//    public function reOrder($orderId)
//    {
//
//        $orderQuery = @Order::where('customer_id', auth()->user()->id);
//
//        if ($orderId) {
//            $orderQuery = @$orderQuery->where('id', $orderId);
//        } else {
//            return $this->responseMsg('Order ID is required', null, 400);
//        }
//
//        $order = $orderQuery->orderBy('id', 'desc')->first();
//        if (!$order) {
//            return $this->responseMsg('Order not found', null, 404);
//        }
////        $data['order'] = OrderResource::make($order);
//
//        $data['products'] = OrderDetailsResource::collection($order->orderDetails);
//        return $this->responseMsg('success fetching data', $data, 200);
//
//    }


    public function reOrder($orderId)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->responseMsg('User not authenticated', null, 401);
        }

        if (!$orderId) {
            return $this->responseMsg('Order ID is required', null, 400);
        }

        $order = Order::where('customer_id', $user->id)
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return $this->responseMsg('Order not found', null, 404);
        }

        $addedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($order->orderDetails as $orderDetail) {
            try {
                // Decode the buy together IDs - these are stored as integers [11622]
                $selectedBuyTogetherIds = [];
                if ($orderDetail->selected_buy_together_ids) {
                    $decoded = json_decode($orderDetail->selected_buy_together_ids, true);
                    // Ensure they remain as integers, not strings
                    $selectedBuyTogetherIds = is_array($decoded) ? array_map('intval', $decoded) : [];
                }

                // Sort for consistent comparison
                sort($selectedBuyTogetherIds);

                // Determine if this is a product or variation
                $productId = $orderDetail->product_id;
                $variantId = $orderDetail->variation_id;

                // Get buy_together_id from the buy_together_ids field
                $buyTogetherId = null;
                if ($orderDetail->buy_together_ids) {
                    $buyTogetherIdsArray = json_decode($orderDetail->buy_together_ids, true);
                    if (is_array($buyTogetherIdsArray) && !empty($buyTogetherIdsArray)) {
                        $buyTogetherId = $buyTogetherIdsArray[0]; // Get first ID
                    }
                }

                // Build base query to check if item already exists in cart
                $query = CartProduct::where('customer_id', $user->id)
                    ->where('product_id', $productId);

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

                // Get all potential matching items
                $cartItems = $query->get();

                // Find exact match by comparing selected_buy_together_ids
                $existingCartItem = null;
                foreach ($cartItems as $item) {
                    $itemBuyTogetherIds = [];

                    if ($item->selected_buy_together_ids) {
                        $decoded = is_string($item->selected_buy_together_ids)
                            ? json_decode($item->selected_buy_together_ids, true)
                            : $item->selected_buy_together_ids;

                        // Ensure integers for comparison
                        $itemBuyTogetherIds = is_array($decoded) ? array_map('intval', $decoded) : [];
                    }

                    sort($itemBuyTogetherIds);

                    if ($itemBuyTogetherIds === $selectedBuyTogetherIds) {
                        $existingCartItem = $item;
                        break;
                    }
                }

                // If exact match found, update quantity
                if ($existingCartItem) {
                    $existingCartItem->update([
                        'quantity' => $existingCartItem->quantity + $orderDetail->qty
                    ]);
                    $addedCount++;
                } else {
                    // Create new cart item
                    // Store as array of integers [11622] not ["11622"]
                    $buyTogetherIdsJson = !empty($selectedBuyTogetherIds)
                        ? json_encode($selectedBuyTogetherIds, JSON_NUMERIC_CHECK)
                        : null;

                    CartProduct::create([
                        'product_id' => $productId,
                        'customer_id' => $user->id,
                        'quantity' => $orderDetail->qty,
                        'variant_id' => $variantId,
                        'selected_buy_together_ids' => $buyTogetherIdsJson,
                        'buy_together_id' => $buyTogetherId,
                    ]);
                    $addedCount++;
                }
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = [
                    'product_id' => $orderDetail->product_id,
                    'error' => $e->getMessage()
                ];
            }
        }

        $message = "Successfully added {$addedCount} product(s) to cart";
        if ($skippedCount > 0) {
            $message .= ". {$skippedCount} product(s) could not be added.";
        }

        $responseData = [
            'added_count' => $addedCount,
            'skipped_count' => $skippedCount,
            'products' => OrderDetailsResource::collection($order->orderDetails)
        ];

        if (!empty($errors)) {
            $responseData['errors'] = $errors;
        }

        return $this->responseMsg($message, $responseData, 200);
    }




//    private function createOrderDetailsFixed($order, $products, $productIds, $quantities, $variations, $buyTogetherIds, $productPrices, $buyTogetherPrices)
//    {
//        foreach ($productIds as $index => $productId) {
//            $product = $products->get($productId);
//
//            if (!$product) {
//                continue;
//            }
//            OrderDetail::create([
//                'order_id' => $order->id,
//                'product_id' => $product->id,
//                'wordpress_product_id' => $productId,
//                'qty' => (int)($quantities[$index] ?? 1), // تحويل لـ integer
//                'variation_id' => !empty($variations[$index]) ? (int)$variations[$index] : null,
//                'buy_together_id' => !empty($buyTogetherIds[$index]) ? (int)$buyTogetherIds[$index] : null,
//                'price' => (float)($productPrices[$index] ?? 0), // تحويل لـ float
//                'buy_together_price' => $buyTogetherPrices[$index] ?? 0, // تحويل لـ float
//            ]);
//        }
//    }



//    private function createOrderDetailsFixed($order, $products, $productIds, $quantities, $variations, $buyTogetherIds, $productPrices, $buyTogetherPrices,$selectedBuyTogetherIds)
//    {
////        dd($buyTogetherIds,$buyTogetherPrices);;
//        foreach ($productIds as $index => $productId) {
//            $product = $products->get($productId);
//
//            if (!$product) {
//                continue;
//            }
////        dd($buyTogetherIds,$buyTogetherPrices);
//            // Convert the nested buy_together_prices (e.g., [365, 675]) to a JSON array or sum them
////            $buyTogetherPriceList = $buyTogetherPrices[$index] ?? [];
////            $buyTogetherPriceList = is_array($buyTogetherPriceList) ? $buyTogetherPriceList : [];
////            // Option 1: Store as JSON (recommended if you need to keep all sub-prices)
////            $buyTogetherPriceValue = json_encode($buyTogetherPriceList, JSON_UNESCAPED_UNICODE);
////            dd($buyTogetherIds,$buyTogetherPrices);
//            $buyTogetherPriceValue = json_encode($buyTogetherPrices);
//
//
//
//
////            $selectedBuyTogetherIdsList = $buyTogetherIds[$index] ?? [];
////            $selectedBuyTogetherIdsList = is_array($selectedBuyTogetherIdsList) ? $selectedBuyTogetherIdsList : [];
////            // Option 1: Store as JSON (recommended if you need to keep all sub-prices)
////            $selectedBuyTogetherIdsValue = json_encode($selectedBuyTogetherIdsList, JSON_UNESCAPED_UNICODE);
//            $selectedBuyTogetherIdsValue = json_encode($selectedBuyTogetherIds);
//
//
//            dd($selectedBuyTogetherIds,$buyTogetherPriceValue);
//            // Option 2 (alternative): store total price instead of JSON
//            // $buyTogetherPriceValue = array_sum($selectedBuyTogetherIdsList);
//
//            OrderDetail::create([
//                'order_id' => $order->id,
//                'product_id' => $product->wordpress_id,
//                'wordpress_product_id' => $productId,
//                'qty' => (int)($quantities[$index] ?? 1),
//                'variation_id' => !empty($variations[$index]) ? (int)$variations[$index] : null,
//                'buy_together_id' => !empty($buyTogetherIds[$index]) ? (int)$buyTogetherIds[$index] : null,
//                'price' => (float)($productPrices[$index] ?? 0),
//                'buy_together_price' => $buyTogetherPriceValue, // stored as JSON or numeric total
//                'selected_buy_together_ids'=>$selectedBuyTogetherIdsValue
//            ]);
//        }
//    }

//    private function createOrderDetailsFixed($order, $products, $productIds, $quantities, $variations, $buyTogetherIds, $productPrices, $buyTogetherPrices, $selectedBuyTogetherIds)
//    {
//        foreach ($productIds as $index => $productId) {
//            $product = $products->get($productId);
//
//            if (!$product) {
//                continue;
//            }
//
//            // Get the buy_together_prices for THIS specific index
//            $buyTogetherPriceList = @$buyTogetherPrices[$index] ?? [];
//            $buyTogetherPriceList = @is_array($buyTogetherPriceList) ? $buyTogetherPriceList : [];
//            $buyTogetherPriceValue = @json_encode($buyTogetherPriceList, JSON_UNESCAPED_UNICODE);
//
//            // Get the selected_buy_together_ids for THIS specific index
//            $selectedBuyTogetherIdsList = @$selectedBuyTogetherIds[$index] ?? [];
//            $selectedBuyTogetherIdsList = @is_array($selectedBuyTogetherIdsList) ? $selectedBuyTogetherIdsList : [];
//            $selectedBuyTogetherIdsValue = @json_encode($selectedBuyTogetherIdsList, JSON_UNESCAPED_UNICODE);
//
//            // Handle buy_together_ids as an array
//            $buyTogetherIdsList = @$buyTogetherIds[$index] ?? [];
//            $buyTogetherIdsList = @is_array($buyTogetherIdsList) ? $buyTogetherIdsList : [];
//            // Store null if array is empty, otherwise encode as JSON
//            $buyTogetherIdsValue = !empty($buyTogetherIdsList) ? @json_encode($buyTogetherIdsList, JSON_UNESCAPED_UNICODE) : null;
//
//            OrderDetail::create([
//                'order_id' => @$order->id,
//                'product_id' => @$product->wordpress_id,
//                'wordpress_product_id' => @$productId,
//                'qty' => (int)($quantities[$index] ?? 1),
//                'variation_id' => !empty($variations[$index]) ? (int)$variations[$index] : null,
//                'buy_together_ids' => $buyTogetherIdsValue, // Now stored as JSON array
//                'price' => (float)($productPrices[$index] ?? 0),
//                'buy_together_price' => $buyTogetherPriceValue,
//                'selected_buy_together_ids' => $selectedBuyTogetherIdsValue
//            ]);
//        }
//    }


    private function createOrderDetailsFixed($order, $products, $productIds, $quantities, $variations, $buyTogetherIds, $productPrices, $buyTogetherPrices, $selectedBuyTogetherIds)
    {
        foreach ($productIds as $index => $productId) {
            $product = $products->get($productId);

            if (!$product) {
                continue;
            }

            // Get the buy_together_prices for THIS specific index
            $buyTogetherPriceList = @$buyTogetherPrices[$index] ?? [];
            $buyTogetherPriceList = @is_array($buyTogetherPriceList) ? $buyTogetherPriceList : [];
            $buyTogetherPriceValue = !empty($buyTogetherPriceList) ? @json_encode($buyTogetherPriceList, JSON_UNESCAPED_UNICODE) : null;

            // Get the selected_buy_together_ids for THIS specific index
            $selectedBuyTogetherIdsList = @$selectedBuyTogetherIds[$index] ?? [];
            $selectedBuyTogetherIdsList = @is_array($selectedBuyTogetherIdsList) ? $selectedBuyTogetherIdsList : [];
            $selectedBuyTogetherIdsValue = !empty($selectedBuyTogetherIdsList) ? @json_encode($selectedBuyTogetherIdsList, JSON_UNESCAPED_UNICODE) : null;

            // Handle buy_together_ids as JSON array (same as the other two)
            $buyTogetherIdsList = @$buyTogetherIds[$index] ?? [];
            // If it's not an array, convert it to an array
            if (!is_array($buyTogetherIdsList)) {
                $buyTogetherIdsList = $buyTogetherIdsList ? [$buyTogetherIdsList] : [];
            }
            $buyTogetherIdsValue = !empty($buyTogetherIdsList) ? @json_encode($buyTogetherIdsList, JSON_UNESCAPED_UNICODE) : null;

            OrderDetail::create([
                'order_id' => @$order->id,
                'product_id' => @$product->wordpress_id,
                'wordpress_product_id' => @$productId,
                'qty' => (int)($quantities[$index] ?? 1),
                'variation_id' => !empty($variations[$index]) ? (int)$variations[$index] : null,
                'buy_together_ids' => $buyTogetherIdsValue, // Store as JSON array
                'price' => (float)($productPrices[$index] ?? 0),
                'buy_together_price' => $buyTogetherPriceValue,
                'selected_buy_together_ids' => $selectedBuyTogetherIdsValue
            ]);
        }
    }


    /**
     * Calculate order total using request prices
     */
//    protected function calculateOrderTotal($products, $quantities, ?string $couponCode, $request): float
//    {
//        $total = 0;
//
//        // استخدام الأسعار والكميات من الـ request
//        foreach ($request->product_price as $index => $price) {
//            $qty = (float)($quantities[$index] ?? 1);
//
//            $productPrice = (float)($price ?? 0);
//            $total += $productPrice * $qty;
//
//            // إضافة سعر buy_together إذا كان موجود
//            $buyTogetherPrice = (float)(@$request->buy_together_price[$index] ?? 0);
//            if ($buyTogetherPrice > 0) {
//                $total += $buyTogetherPrice * $qty;
//            }
//        }
//
//        return $total;
//    }



//    protected function calculateOrderTotal($products, $quantities, ?string $couponCode, $request): float
//    {
////        dd($products,$request->buy_together_price,$request->selected_buy_together_ids);
//        $total = 0;
//
//
//        $coupon=$request->coupon_code;
//
//
//
//
//        foreach ($request->product_price as $index => $price) {
//            $qty = (float)($quantities[$index] ?? 1);
//            $productPrice = (float)($price ?? 0);
//
//            $total += $productPrice * $qty;
//
//
//
//            // السعر الأساسي
//
//            // إضافة أسعار buy_together (لو فيه)
//            if (!empty($request->buy_together_price[$index])) {
//                foreach ($request->buy_together_price[$index] as $btPrice) {
//                    $btPrice = (float)($btPrice ?? 0);
//                    $total += $btPrice * $qty;
//                }
//            }
//
//
//            if ($coupon){
//
//
//                if ($coupon->discount_type =='fixed_cart' ) {
//                    $total-=$coupon->discount;
//                }elseif($coupon->discount_type =='percent' ){
//                    $total = $total * ($coupon->discount / 100);
//                }else if($coupon->discount_type =='fixed_product' ){
////                    if (json_decode($coupon->product_ids)->count() > 0 &&json_decode($coupon->product_ids)->contains($products[$index]->wordpress_id)) {// the discount will also be not applied to the product if the coupon type is fixed cart
////                            $total = $total - $coupon->discount;
////                    }
////                    if (json_decode($coupon->excluded_product_ids)->count() > 0 && !json_decode($coupon->excluded_product_ids)->contains($products[$index]->wordpress_id)) {// the discount will also be not applied to the product if the coupon type is fixed cart
////                            $total = $total - $coupon->discount;
////                    }
////                    $productCategoryIds=json_decode(Product::where('wordpress_id',$products[$index]->wordpress_id)->first()->category_ids);
////                    if (json_decode($coupon->product_categories)->count() > 0 &&json_decode($coupon->product_categories)->contains($products[$index]->wordpress_id)) {// the discount will also be not applied to the product if the coupon type is fixed cart
////                        $total = $total - $coupon->discount;
////                    }
////                    if (json_decode($coupon->excluded_product_categories)->count() > 0 && !json_decode($coupon->excluded_product_categories)->contains($products[$index]->wordpress_id)) {// the discount will also be not applied to the product if the coupon type is fixed cart
////                        $total = $total - $coupon->discount;
////                    }
//
//                    $discount_applied=0;// 0 =>still checking (contrinue) ,1=> excluded , 2=> excluded
//
//                    $product = Product::where('wordpress_id', $products[$index]->wordpress_id)->first();
//
//                    $productCategoryIds = collect(json_decode($product->category_ids ?? '[]'));
//                    $couponProductIds = collect(json_decode($coupon->product_ids ?? '[]'));
//                    $couponExcludedProductIds = collect(json_decode($coupon->excluded_product_ids ?? '[]'));
//                    $couponCategoryIds = collect(json_decode($coupon->product_categories ?? '[]'));
//                    $couponExcludedCategoryIds = collect(json_decode($coupon->excluded_product_categories ?? '[]'));
//
//
//                    if ($couponProductIds->count() > 0 && $couponProductIds->contains($product->wordpress_id)&& $discount_applied!=1) {
//                        $discount_applied=2;
//                    }
//
//                    if ($couponExcludedProductIds->count() > 0 && !$couponExcludedProductIds->contains($product->wordpress_id) ) {
//                        $discount_applied=1;
//                        $total -= $coupon->discount;
//                    }
//
//                    if ($couponExcludedCategoryIds->count() > 0 && $productCategoryIds->intersect($couponExcludedCategoryIds)->isEmpty()&& $discount_applied!=1&& $discount_applied!=2) {
//                        $discount_applied=2;
//
//                    }
//
//                    if ($couponCategoryIds->count() > 0 && $productCategoryIds->intersect($couponCategoryIds)->isNotEmpty()&& $discount_applied!=1&& $discount_applied!=2) {
//                        $total -= $coupon->discount;
//                    }
//
//
//
//
//                }
//                }else{
//                    return $this->responseMsg('Invalid coupon code', null, 400);
//                }
//            }
//
//
//        return $total;
//    }


//    protected function calculateOrderTotal($products, $quantities, ?string $couponCode, $request): float
//    {
//        $total = 0;
//
//        $coupon = $request->coupon_code;
//
//        foreach ($request->product_price as $index => $price) {
//            $qty = (float)($quantities[$index] ?? 1);
//            $productPrice = (float)($price ?? 0);
//
//            $product = Product::where('wordpress_id', $products[$index]->wordpress_id)->first();
//            $total += $productPrice * $qty;
//
//            // Include "buy together" items
//            $buyTogetherPrices = $request->buy_together_price[$index] ?? [];
//            foreach ($buyTogetherPrices as $btPrice) {
//                $btPrice = (float)($btPrice ?? 0);
//                $total += $btPrice * $qty;
//            }
//
//            // -------------------------------
//            // Apply coupon if exists
//            // -------------------------------
//            if ($coupon) {
//                switch ($coupon->discount_type) {
//                    case 'fixed_cart':
//                        $total -= $coupon->discount;
//                        break;
//
//                    case 'percent':
//                        $total -= ($total * $coupon->discount / 100);
//                        break;
//
//                    case 'fixed_product':
//                        // Apply to main product and its buy-together products
//                        $discount_applied = false;
//
//                        $productCategoryIds = collect(json_decode($product->category_ids ?? '[]'));
//                        $couponProductIds = collect(json_decode($coupon->product_ids ?? '[]'));
//                        $couponExcludedProductIds = collect(json_decode($coupon->excluded_product_ids ?? '[]'));
//                        $couponCategoryIds = collect(json_decode($coupon->product_categories ?? '[]'));
//                        $couponExcludedCategoryIds = collect(json_decode($coupon->excluded_product_categories ?? '[]'));
//
//                        // ---- check product-level rules ----
//                        $applyDiscount = false;
//
//                        if ($couponProductIds->count() > 0 && $couponProductIds->contains($product->wordpress_id)) {
//                            $applyDiscount = true;
//                        }
//
//                        if ($couponExcludedProductIds->count() > 0 && $couponExcludedProductIds->contains($product->wordpress_id)) {
//                            $applyDiscount = false;
//                        }
//
//                        if ($couponCategoryIds->count() > 0 && $productCategoryIds->intersect($couponCategoryIds)->isNotEmpty()) {
//                            $applyDiscount = true;
//                        }
//
//                        if ($couponExcludedCategoryIds->count() > 0 && $productCategoryIds->intersect($couponExcludedCategoryIds)->isNotEmpty()) {
//                            $applyDiscount = false;
//                        }
//
//                        if ($applyDiscount) {
//                            $total -= $coupon->discount;
//                        }
//
//                        // Apply discount to "buy together" items
//                        foreach ($buyTogetherPrices as $btPrice) {
//                            if ($applyDiscount) {
//                                $total -= $coupon->discount;
//                            }
//                        }
//
//                        break;
//
//                    default:
//                        return $this->responseMsg('Invalid coupon code', null, 400);
//                }
//            }
//        }
//
//        return max($total, 0); // Prevent negative totals
//    }





    protected function calculateOrderTotal($products, $quantities, ?string $couponCode, $request): float
    {
        $total = 0;

//        $coupon = $request->coupon_code;
        $coupon = null;
        if ($request->coupon_code) {
            $coupon = \App\Models\Coupon::where('code', $request->coupon_code)->first();
        }




//        foreach ($request->product_price as $index => $price) {
//            $qty = (float)($quantities[$index] ?? 1);
//            $productPrice = (float)($price ?? 0);
//
//            // 🔹 Fetch the main product (could be Product or DigitalProductVariation)
//            $product = $this->findProductOrVariation($products[$index]->wordpress_id);
//            $total += $productPrice * $qty;
//
//            // 🔹 Include "buy together" prices
//            $buyTogetherPrices = $request->buy_together_price[$index] ?? [];
//            $buyTogetherIds = $request->selected_buy_together_ids[$index] ?? [];
//
//            foreach ($buyTogetherPrices as $btIndex => $btPrice) {
//                $btPrice = (float)($btPrice ?? 0);
//                $total += $btPrice * $qty;
//            }
//
//            // -------------------------------
//            // 🔸 Apply coupon logic (if exists)
//            // -------------------------------
//            if ($coupon) {
//                switch ($coupon->discount_type) {
//                    case 'fixed_cart':
//                        $total -= $coupon->discount;
//                        break;
//
//                    case 'percent':
//                        $total -= ($total * $coupon->discount / 100);
//                        break;
//
//                    case 'fixed_product':
//                        // check eligibility for the main product
//                        if ($this->isEligibleForCoupon($product, $coupon)) {
//                            $total -= $coupon->discount;
//                        }
//
//                        // check eligibility for buy-together products
//                        foreach ($buyTogetherIds as $btId) {
//                            $btProduct = $this->findProductOrVariation($btId);
//                            if ($btProduct && $this->isEligibleForCoupon($btProduct, $coupon)) {
//                                $total -= $coupon->discount;
//                            }
//                        }
//                        break;
//
//                    default:
//                        return $this->responseMsg('Invalid coupon code', null, 400);
//                }
//            }
//        }
//dd($request->product_price);
//        foreach ($request->product_price as $index => $price) {
//            // Skip if product not set
////            dd($index,$products,$price);
//            if (!isset($products[$index])) continue;
//
//            $qty = (integer)($quantities[$index] ?? 1);
//            $productPrice = (float)($price ?? 0);
//            $total += $productPrice * $qty;
//
//            // ✅ Handle buy together safely
//            if (!empty($request->buy_together_price[$index]) && is_array($request->buy_together_price[$index])) {
//                foreach ($request->buy_together_price[$index] as $btIndex => $btPrice) {
//                    $btPrice = (float)($btPrice ?? 0);
//                    $total += $btPrice * $qty;
//
//                    // Check if buy_together_id exists at this index
//                    if (!empty($request->selected_buy_together_ids[$index][$btIndex])) {
//                        $btId = $request->selected_buy_together_ids[$index][$btIndex];
//
//                        // Find the product in either table
//                        $btProduct = Product::where('wordpress_id', $btId)->first()
//                            ?? DigitalProductVariation::where('wordpress_id', $btId)->first();
//
//                        if ($btProduct && $coupon) {
//                            // apply same coupon logic here
//                            $total -= $this->getDiscountAmount($btProduct, $coupon);
//                        }
//                    }
//                }
//            }
//
//            // Apply coupon to main product
//            if ($coupon) {
//                $product = Product::where('wordpress_id', $products[$index]->wordpress_id)->first()
//                    ?? DigitalProductVariation::where('wordpress_id', $products[$index]->wordpress_id)->first();
//
//                if ($product) {
//                    $total -= $this->getDiscountAmount($product, $coupon);
//                }
//            }
//        }






        foreach ($request->product_price as $index => $price) {
            // Get product ID for this index
            $productId = $request->product_ids[$index] ?? null;

            if (!$productId || !isset($products[$productId])) continue;

            $productData = (object)$products[$productId]; // convert to object for easier access
            $qty = (int)($quantities[$index] ?? 1);
            $productPrice = (float)($price ?? 0);

            $total += $productPrice * $qty;

            $fixed_product_x_limit=0;

            // ✅ Handle buy together products
            if (!empty($request->buy_together_price[$index]) && is_array($request->buy_together_price[$index])) {
                foreach ($request->buy_together_price[$index] as $btIndex => $btPrice) {
                    if ($coupon){
                        if ($coupon->discount_type=='fixed_product'){
                            $fixed_product_x_limit++;
                        }
                    }

                    $btPrice = (float)($btPrice ?? 0);
                    $total += $btPrice * $qty;
                    if ($coupon){
                        if (!empty($request->selected_buy_together_ids[$index][$btIndex])) {
                            $btId = $request->selected_buy_together_ids[$index][$btIndex];

                            // find buy together product
                            $btProduct = Product::where('wordpress_id', $btId)->first()
                                ?? DigitalProductVariation::where('wordpress_id', $btId)->first();
                            if (($total<@$coupon->minimum_amount||$total>@$coupon->maximum_amount)&&$coupon->maximum_amount>0){
                                return -1;
                            }
                            if ($btProduct && $coupon && (@$coupon->limit_usage_to_x_items==0 || $fixed_product_x_limit<@$coupon->limit_usage_to_x_items)) {
                                $total -= $this->getDiscountAmount($btProduct, $coupon);
                            }
                        }
                    }

                }
            }

            // ✅ Apply coupon to main product
            if ($coupon) {
                if (($total<@$coupon->minimum_amount||$total>@$coupon->maximum_amount)&&$coupon->maximum_amount>0){
                    return -1;
                }
                $product = Product::where('wordpress_id', $productData->wordpress_id)->first()
                    ?? DigitalProductVariation::where('wordpress_id', $productData->wordpress_id)->first();

                if ($product  && ($coupon->limit_usage_to_x_items==0 || $fixed_product_x_limit<$coupon->limit_usage_to_x_items))  {
                    $total -= $this->getDiscountAmount($product, $coupon ,$total);
                }
            }
        }
        $total+=$request->shipping_fee;

        return max($total, 0); // prevent negatives
    }
    protected function getDiscountAmount($product, $coupon): float
    {
        $discount = 0;

//        exclude_sale_items

        $productCategoryIds = collect(json_decode($product->category_ids ?? '[]'));
        $couponProductIds = collect(json_decode($coupon->product_ids ?? '[]'));
        $couponExcludedProductIds = collect(json_decode($coupon->excluded_product_ids ?? '[]'));
        $couponCategoryIds = collect(json_decode($coupon->product_categories ?? '[]'));
        $couponExcludedCategoryIds = collect(json_decode($coupon->excluded_product_categories ?? '[]'));

        if ($coupon->discount_type === 'fixed_product') {
            $minimum_amount=$coupon->minimum_amount;
            $maximum_amount=$coupon->maximum_amount;
            // Check if excluded
            if ($couponExcludedProductIds->contains($product->wordpress_id) ||
                $productCategoryIds->intersect($couponExcludedCategoryIds)->isNotEmpty()) {
                return 0;
            }

            // Check if included
            if ($couponProductIds->isEmpty() && $couponCategoryIds->isEmpty()) {
                $discount = $coupon->discount;
            } elseif ($couponProductIds->contains($product->wordpress_id) ||
                $productCategoryIds->intersect($couponCategoryIds)->isNotEmpty()) {
                $discount = $coupon->discount;
            }
//            dd($discount);
            $reached_limit++;
        } elseif ($coupon->discount_type === 'percent') {
            $discount = $product->price * ($coupon->discount / 100);
        } elseif ($coupon->discount_type === 'fixed_cart') {
//            $discount = $coupon->discount;


            if ($couponExcludedProductIds->contains($product->wordpress_id) ||
                $productCategoryIds->intersect($couponExcludedCategoryIds)->isNotEmpty()) {
                return 0;
            }

            // Check if included
            if ($couponProductIds->isEmpty() && $couponCategoryIds->isEmpty()) {
                $discount = $coupon->discount;
            } elseif ($couponProductIds->contains($product->wordpress_id) ||
                $productCategoryIds->intersect($couponCategoryIds)->isNotEmpty()) {
                $discount = $coupon->discount;
            }
        }

        return $discount;
    }

    /**
     * 🔍 Find a product or variation by its wordpress_id
     */
    protected function findProductOrVariation($wordpressId)
    {
        return Product::where('wordpress_id', $wordpressId)->first()
            ?? DigitalProductVariation::where('wordpress_id', $wordpressId)->first();
    }

    /**
     * 🧠 Check if a given product (or variation) is eligible for a given coupon
     */
    protected function isEligibleForCoupon($product, $coupon): bool
    {
        $productCategoryIds = collect(json_decode($product->category_ids ?? '[]'));
        $couponProductIds = collect(json_decode($coupon->product_ids ?? '[]'));
        $couponExcludedProductIds = collect(json_decode($coupon->excluded_product_ids ?? '[]'));
        $couponCategoryIds = collect(json_decode($coupon->product_categories ?? '[]'));
        $couponExcludedCategoryIds = collect(json_decode($coupon->excluded_product_categories ?? '[]'));

        // Step 1: check excluded product
        if ($couponExcludedProductIds->contains($product->wordpress_id)) {
            return false;
        }

        // Step 2: check excluded category
        if ($couponExcludedCategoryIds->count() > 0 && $productCategoryIds->intersect($couponExcludedCategoryIds)->isNotEmpty()) {
            return false;
        }

        // Step 3: if specific product IDs are defined, must be included
        if ($couponProductIds->count() > 0) {
            return $couponProductIds->contains($product->wordpress_id);
        }

        // Step 4: if specific categories are defined, must match one
        if ($couponCategoryIds->count() > 0) {
            return $productCategoryIds->intersect($couponCategoryIds)->isNotEmpty();
        }

        // Default: eligible
        return true;
    }










    /**
     * Apply coupon discount
     */
    protected function applyCoupon(?string $couponCode, $order, float $total): float
    {
        if (!$couponCode) {
            return $total;
        }

        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon || $coupon->status != 1 || $coupon->expire_date < now() ||
            $coupon->usage_count >= ($coupon->users_limit * $coupon->limit)) {
            return $total;
        }

        $discountAmount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discountAmount = ($total * $coupon->discount) / 100;
        } else {
            $discountAmount = min($coupon->discount, $total);
        }

        $finalTotal = max(0, $total - $discountAmount);

        // تحديث الطلب بالسعر النهائي
        $order->update(['paid_amount' => $finalTotal]);

        return $finalTotal;
    }


    public function updateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(implode(' ', $validator->errors()->all()), null, 403);
        }

        $order = Order::where('id', $request->order_id)->first();
        if (!$order) {
            return $this->responseMsg('Order was not found', null, 404);
        }

        if (!$order->customer_id == auth()->id()) {
            return $this->responseMsg('You are not authorized to update this order', null, 403);
        }

        if ($order->payment_status == 'paid') {
            return $this->responseMsg('Order is already paid', OrderResource::make($order), 201);
        }

        $order->update([
            'payment_status' => 'paid'
        ]);

        return $this->responseMsg('Order status updated successfully', OrderResource::make($order), 200);
    }


    protected function syncOrderToWooCommerce(Order $order, $products)
    {
        try {
            // Prepare the API endpoint and credentials
            $endpoint = config('services.woocommerce.url') . '/wp-json/wc/v3/orders';
            $consumerKey = config('services.woocommerce.key');
            $consumerSecret = config('services.woocommerce.secret');

            if (empty($endpoint) || empty($consumerKey) || empty($consumerSecret)) {
                throw new \Exception('WooCommerce API credentials not configured');
            }

            $orderData = $this->prepareWooCommerceOrderData($order, $products);

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($consumerKey, $consumerSecret)
                ->timeout(30)
                ->retry(3, 100)
                ->post($endpoint, $orderData);
            if ($response->successful()) {
                $woocommerceOrder = $response->json();
                $order->update(['wordpress_id' => $woocommerceOrder['id']]);
                Log::info("Order #{$order->id} synced to WooCommerce successfully");
                return true;
            }

            $errorDetails = [
                'order_id' => $order->id,
                'status' => $response->status(),
                'response' => $response->body(),
                'payload' => $orderData
            ];

            Log::error('WooCommerce sync failed', $errorDetails);
            throw new \Exception('WooCommerce order creation failed');
        } catch (\Exception $e) {
            Log::error('WooCommerce sync exception: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    protected function prepareWooCommerceOrderData(Order $order, $products): array
    {
        $customer = $order->customer;

        return [
            'payment_method' => str_replace('_', ' ', $order->payment_method),
            'payment_method_title' => str_replace('_', ' ', $order->payment_method),
            'status' => 'processing',
            'set_paid' => true,
            // 'customer_id' => $customer->wordpress_id ?? 0,
            'billing' => [
                'first_name' => $customer->f_name,
                'last_name' => $customer->l_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address_1' => $order->shipping_address_data ?? '',
                'city' => $order->shipping_city_id ?? '',
                'state' => $order->shipping_state_id ?? '',
                'postcode' => $order->shipping_postal_code ?? '',
                'country' => $order->shipping_address ?? 'Egypt',
            ],
            'shipping' => [
                "first_name" => $customer->f_name,
                "last_name" => $customer->l_name,
                "address_1" => $order->shipping_address_data,
                "address_2" => "",
                "city" => $order->shipping_city_id,
                "state" => $order->shipping_state_id,
                "postcode" => $order->postal_code,
                "country" => "Egypt"
            ],
            'line_items' => $products ? $products->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation'],
                    'quantity' => $item['qty'],
                ];
            })->toArray() : [],
            'coupon_lines' => $products ? $products->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation'],
                    'quantity' => $item['qty'],
                ];
            })->toArray() : [],
            'shipping_lines' => [
                [
                    'method_id' => 'flat_rate',
                    'customer_id' => $customer->wordpress_id ?? 0,
                    'paid_amount' => $order->paid_amount,
                    'method_title' => 'Flat Rate',
                    'total' => (string)($order->paid_amount ?? 0)
                ]
            ],
        ];
    }

    protected function syncCouponToWooCommerce(Coupon $coupon)
    {
        $couponData = [
            'used_by' => [
                "4643",
                "abdosalah00@gmail.com",
                "nouran_mohammed_hassan@live.com",
                "4643",
                "kareem.tobgy@gmail.com",
                "nouran_moghazy306@gmail.com",
                "4643",
                "6849"
            ]
        ];
    }


    protected function getPaymentMethodTitle($method)
    {
        $titles = [
            'card_on_delivery' => 'Card on Delivery',
            'wallet' => 'Mobile Wallet',
            'cash_on_delivery' => 'Cash on Delivery',
            'paymob' => 'Paymob',
            'instapay' => 'Instapay'
        ];

        return $titles[$method] ?? $method;
    }

    protected function calculateShippingCost($products, $quantities)
    {
        // Implement your shipping cost calculation logic here
        return 50; // Example flat rate
    }

    /**
     * Create order details
     */
    protected function createOrderDetails($order, $products, $quantities, $variations, $buy_together_ids, $product_prices, $buy_together_prices): void
    {
//        dd($product_prices);
        foreach ($products as $index => $product) {
            $qty = $quantities[$index] ?? 1;
            $variation = $variations[$index] ?? null;
            $buy_together_id = $buy_together_ids[$index] ?? null;
            $product_price = $product_prices[$index] ?? null;
            $buy_together_price = $buy_together_prices[$index] ?? null;
            $order->details()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => $product_price ?? 0,
                'buy_together_id' => $buy_together_id,
                'buy_together_price' => $buy_together_price,
                'qty' => $qty,
                'variation_id' => $variation,
                'discount' => $product->discount ?? 0,
//              'product_details' => $product->details,
            ]);
        }
    }

    /**
     * Handle payment based on method
     */
    protected function handlePayment(string $method, float $amount): array
    {
        // For mobile cash payments, receipt upload is already validated
        if (in_array($method, ['wallet'])) { //'vodafone_cash', 'orange_cash', 'etisalat_cash','instapay'
            return ['success' => true];
        }

        // For Paymob payment gateway
        if ($method === 'paymob') {
            return $this->generatePaymentUrl($amount);
        }

        // For cash on delivery methods
        return ['success' => true];
    }


    public function getPaymentMethods()
    {
        $offlineMethods = OfflinePaymentMethod::get();
        $data['instapay'] = OfflinePaymentMethodResource::collection($offlineMethods)->where('method_name', 'instapay')->first();
        $data['vodafone_cash'] = OfflinePaymentMethodResource::collection($offlineMethods)->where('method_name', 'vodafone_cash')->first();
        $data['orange_cash'] = OfflinePaymentMethodResource::collection($offlineMethods)->where('method_name', 'orange_cash')->first();
        $data['etisalat_cash'] = OfflinePaymentMethodResource::collection($offlineMethods)->where('method_name', 'etisalat_cash')->first();
        return $this->responseMsg(
            'Payment methods retrieved successfully.',
            $data,
            status: 200
        );
    }

    public function call_back(Request $request)
    {
        $orderId = $request->input('order_id');
        if (!$orderId) {
            return response()->json([
                'message' => 'Order ID is required.',
                'status' => 400,
                'data' => []
            ]);
        }

        $status = $this->checkPaymentStatus($orderId);

        if (!$status['success']) {
            return response()->json([
                'message' => $status['message'] ?? 'Payment status check failed.',
                'status' => 500,
                'data' => []
            ]);
        }

        if (!$status['is_paid']) {
            return response()->json([
                'message' => 'Payment not completed yet.',
                'status' => 402,
                'data' => []
            ]);
        }

        try {
            DB::beginTransaction();

            $order = Order::where("customer_id", auth()->id())
                ->latest()
                ->first();

            if ($order && $order->order_status !== 'paid') {
                $order->update(['order_status' => 'paid']);

                $user = $order->customer;
                if ($user) {
                    PaymentHestory::create([
                        'order_id' => $order->id,
                        'user_id' => $user->id,
                        'amount' => $status['amount'],
                        'payment_method' => 'PayMob',
                        'transaction_id' => $orderId,
                        'status' => 'success',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment confirmed successfully.',
                'status' => 200,
                'data' => []
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Payment confirmation failed. ' . $e->getMessage(),
                'status' => 500,
                'data' => []
            ]);
        }
    }

    public function rateOrder(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'nullable|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);
        try {
            $order = Order::find($request->input('order_id') ?? '');
            if ($order->is_rated == 1) {
                return $this->responseMsg(
                    'Order has already been rated.',
                    null,
                    400,
                );
            }

            DB::beginTransaction();

            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.',
                    'status' => 404,
                    'data' => []
                ]);
            }
            foreach ($order->details as $detail) {
                $review = Review::create([
                    'order_id' => $order->id,
                    'customer_id' => auth()->id(),
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                    'product_id' => $detail->product_id ?? null,
                ]);

                $averageRating = Review::where('product_id', $detail->product_id)->avg('rating');

                Product::where('id', $detail->product_id)->update([
                    'average_rating' => ($averageRating / 5) * 5,
                ]);
            }
            $order->update(['is_rated' => 1]);
            DB::commit();

            return $this->responseMsg(
                'Order been rated successfully.',
                null,
                200,

            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseMsg(
                'Failed to rate order. ' . $e->getMessage(),
                null,
                500,
            );
        }
    }

    public function getOrderProducts(Request $request)
    {
        $order = Order::find($request->order_id);
        if (!$order) {
            return $this->responseMsg(
                'Order not found.',
                null,
                404
            );
        }
        $data = $order->details->map(function ($detail) {
            $productResource = new ProductResource($detail->productAllStatus, $detail->qty);
            return $productResource;
        });
        return $this->responseMsg(
            'products has been returned successfully.',
            $data,
            200
        );
    }


    public function cancelOrder(Request $request)
    {
        $order = Order::find($request->order_id);
        if ($order && auth()->check() && auth()->user()->id == $order->customer_id) {
            $order = Order::find($request->order_id);

            $order->update(['order_status' => 'canceled']);
            return $this->responseMsg(
                'Order has been canceled successfully.',
                null,
                200
            );
        } elseif (!$order) {
            return $this->responseMsg(
                'Order not found.',
                null,
                404
            );
        } elseif (auth()->user()->id != $order->customer_id || auth()->check() == false) {
            return $this->responseMsg(
                'you do not have permission to cancel this order.',
                null,
                404
            );
        }
    }
}
