<?php

namespace App\Http\Controllers\RestAPI\v5\orders;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MaximPaymentController
{
    private $merchantId;
    private $password;
    private $apiVersion;
    private $gatewayUrl;

    public function __construct()
    {
        $this->merchantId = env('MPGS_MERCHANT_ID');
        $this->password   = env('MPGS_PASSWORD');
        $this->apiVersion = env('MPGS_API_VERSION', 66);
        $this->gatewayUrl = rtrim(env('MPGS_URL'), '/') . "/api/rest/version/{$this->apiVersion}/merchant/{$this->merchantId}";
    }

    /**
     * Step 1 - Create Session
     */
    public function createSession(Request $request)
    {
        $orderId  = "ORDER_" . time(); // Unique order id
        $amount   = $request->input('amount', '100.00');
        $currency = $request->input('currency', 'EGP');

        // Step 1: Create session only
        $createResponse = Http::withBasicAuth("merchant.{$this->merchantId}", $this->password)
            ->post("{$this->gatewayUrl}/session", [
                'apiOperation' => 'CREATE_SESSION',
            ]);

        if ($createResponse->failed()) {
            return response()->json(['error' => $createResponse->body()], 400);
        }

        $sessionData = $createResponse->json();
        $sessionId   = $sessionData['session']['id'] ?? null;

        if (!$sessionId) {
            return response()->json(['error' => 'Session not created'], 400);
        }

        // Step 2: Update session with order details
        $updateResponse = Http::withBasicAuth("merchant.{$this->merchantId}", $this->password)
            ->put("{$this->gatewayUrl}/session/{$sessionId}", [
                'order' => [
                    'id'       => $orderId,
                    'amount'   => $amount,
                    'currency' => $currency,
                ],
            ]);

        if ($updateResponse->failed()) {
            return response()->json(['error' => $updateResponse->body()], 400);
        }

        return response()->json([
            'orderId'   => $orderId,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Step 3 - Get Order Status
     */
    public function checkStatus($orderId)
    {
        $response = Http::withBasicAuth("merchant.{$this->merchantId}", $this->password)
            ->get("{$this->gatewayUrl}/order/{$orderId}");

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        $data = $response->json();

        return response()->json([
            'orderId' => $orderId,
            'status'  => $data['result'] ?? 'UNKNOWN',
            'details' => $data,
        ]);
    }
}