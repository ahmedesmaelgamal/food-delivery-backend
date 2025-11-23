<?php

namespace App\Http\Controllers\Wordpress;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Jobs\HandleWordpressCouponWebhook;
use App\Jobs\HandleWordpressOrderWebhook;
use App\Jobs\HandleWordpressProductWebhook;
use App\Jobs\HandleWordpressCustomerWebhook;
//use App\Jobs\HandleWordpressUserWebhook;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class WordPressWebhookController extends Controller
{
    // Order webhooks
    public function orderCreate(Request $request): JsonResponse
    {
        Log::info('WooCommerce order created webhook received', $request->all());

        HandleWordPressOrderWebhook::dispatchAfterResponse(
            $request->all(),
            'create'
        );

        return response()->json(['message' => 'Order create webhook accepted'], 202);
    }

    public function orderUpdate(Request $request): JsonResponse
    {
        Log::info('WooCommerce order updated webhook received', $request->all());

        HandleWordPressOrderWebhook::dispatchAfterResponse(
            $request->all(),
            'update'
        );

        return response()->json(['message' => 'Order update webhook accepted'], 202);
    }
    public function orderDelete(Request $request): JsonResponse
    {
        Log::info('WooCommerce order delete webhook received', $request->all());

        HandleWordPressOrderWebhook::dispatchAfterResponse(
            $request->all(),
            'delete'
        );

        return response()->json(['message' => 'Order delete webhook accepted'], 202);
    }
    

    // Product webhooks
    public function productCreate(Request $request): JsonResponse
    {
        Log::info('WooCommerce product created webhook received', $request->all());

        HandleWordPressProductWebhook::dispatchAfterResponse(
            $request->all(),
            'create'
        );

        return response()->json(['message' => 'Product create webhook accepted'], 202);
    }

    public function productUpdate(Request $request): JsonResponse
    {
        Log::info('WooCommerce product update webhook received', $request->all());

        HandleWordPressProductWebhook::dispatchAfterResponse(
            $request->all(),
            'update'
        );

        return response()->json(['message' => 'Product update webhook accepted'], 202);
    }
    public function productDelete(Request $request): JsonResponse
    {
        Log::info('WooCommerce product delete webhook received', $request->all());

        HandleWordPressProductWebhook::dispatchAfterResponse(
            $request->all(),
            'delete'
        );

        return response()->json(['message' => 'Product delete webhook accepted'], 202);
    }
    
    

    // Customer webhooks
//    public function customerCreate(Request $request): JsonResponse
//    {
//        Log::info('WooCommerce customer create webhook received', $request->all());
//        dd($request->all());
//        HandleWordPressCustomerWebhook::dispatchAfterResponse(
//            $request->all(),
//            'create'
//        );
//
//        return response()->json(['message' => 'Customer create webhook accepted'], 202);
//    }



    public function customerCreate(Request $request): JsonResponse
    {
        \Log::info('WooCommerce customer create webhook received debug', [
            'all'        => $request->all(),          // parsed input
            'content'    => $request->getContent(),  // raw body
            'php_input'  => file_get_contents('php://input'),
            'headers'    => $request->headers->all(),
            'content-type' => $request->header('Content-Type'),
            'method'     => $request->method(),
            'client_ip'  => $request->ip(),
        ]);

        // Try to decode raw JSON explicitly so you can dispatch a real array
        $raw = $request->getContent();
        $decoded = json_decode($raw, true);

        \Log::info('json decode check', [
            'decoded' => $decoded,
            'json_error' => json_last_error(),
            'json_error_msg' => json_last_error_msg(),
        ]);

        // Dispatch using the explicit decoded array to be safe
        $payload = is_array($decoded) ? $decoded : $request->all();
        HandleWordPressCustomerWebhook::dispatchAfterResponse($payload, 'create');

        return response()->json(['message' => 'Customer create webhook accepted'], 202);
    }


    public function customerUpdate(Request $request): JsonResponse
    {
        Log::info('WooCommerce customer update webhook received', $request->all());

        HandleWordPressCustomerWebhook::dispatchAfterResponse(
            $request->all(),
            'update'
        );

        return response()->json(['message' => 'Customer update webhook accepted'], 202);
    }
    public function customerDelete(Request $request): JsonResponse
    {
        Log::info('WooCommerce customer delete webhook received', $request->all());

        HandleWordPressCustomerWebhook::dispatchAfterResponse(
            $request->all(),
            'delete'
        );

        return response()->json(['message' => 'Customer delete webhook accepted'], 202);
    }



    public function couponCreate(Request $request): JsonResponse
    {
        Log::info('WooCommerce coupon create webhook received', $request->all());

        HandleWordPressCouponWebhook::dispatchAfterResponse(
            $request->all(),
            'create'
        );

        return response()->json(['message' => 'coupon create webhook accepted'], 202);
    }

    public function couponUpdate(Request $request): JsonResponse
    {
        Log::info('WooCommerce coupon update webhook received', $request->all());

        HandleWordPressCouponWebhook::dispatchAfterResponse(
            $request->all(),
            'update'
        );

        return response()->json(['message' => 'coupon update webhook accepted'], 202);
    }
    public function couponDelete(Request $request): JsonResponse
    {
        Log::info('WooCommerce coupon delete webhook received', $request->all());

        HandleWordPressCouponWebhook::dispatchAfterResponse(
            $request->all(),
            'delete'
        );

        return response()->json(['message' => 'coupon delete webhook accepted'], 202);
    }
}
