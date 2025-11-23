<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client;
use App\Models\PaymentLog;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait PayMobTrait
{
    public function generatePaymentUrl($total_price)
    {
        try {
            $user = auth()->user();
            $nameParts = explode(' ', $user->name ?? 'Maxim User');
            $first_name = $nameParts[0] ?? 'Maxim';
            $last_name = $nameParts[1] ?? 'User';

            if ($total_price <= 0) {
                return ['success' => false, 'message' => 'Total price must be greater than 0.'];
            }

            // Get token
            $client = new Client(['timeout' => 30]);
            $authResponse = $client->post('https://accept.paymob.com/api/auth/tokens', [
                'json' => [
                    'username' => config('services.paymob.username'),
                    'password' => config('services.paymob.password'),
                ]
            ]);
            $authData = json_decode($authResponse->getBody(), true);

            // Get payment link
            $paymentResponse = $client->post('https://accept.paymob.com/api/ecommerce/payment-links', [
                'headers' => ['Authorization' => 'Bearer ' . $authData['token']],
                'json' => [
                    'amount_cents' => intval($total_price * 100),
                    'currency' => 'EGP',
                    'is_live' => false,
                    'payment_methods' => [config('services.paymob.integration_id')],
                    'full_name' => "$first_name $last_name",
                    'email' => $user->email ?? 'test@email.com',
                    'phone_number' => '+' . ($user->phone ?? '201111111111'),
                ]
            ]);

            $paymentData = json_decode($paymentResponse->getBody(), true);

            return [
                'success' => true,
                'payment_url' => $paymentData['client_url'],
                'order_id' => $paymentData['order'],
            ];
        } catch (Exception $e) {
            Log::error('PayMob generatePaymentUrl Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkPaymentStatus($orderId)
    {
        try {
            $client = new Client(['timeout' => 30]);

            // Get token
            $authResponse = $client->post('https://accept.paymob.com/api/auth/tokens', [
                'json' => [
                    'username' => config('services.paymob.username'),
                    'password' => config('services.paymob.password')
                ]
            ]);
            $authData = json_decode($authResponse->getBody(), true);

            // Inquiry
            $inquiryResponse = $client->post('https://accept.paymob.com/api/ecommerce/orders/transaction_inquiry', [
                'headers' => ['Authorization' => 'Bearer ' . $authData['token']],
                'json' => ['order_id' => $orderId]
            ]);
            $inquiryData = json_decode($inquiryResponse->getBody(), true);

            $isPaid = isset($inquiryData['order']['payment_status']) && $inquiryData['order']['payment_status'] === 'PAID';

            PaymentLog::create([
                'log' => json_encode($inquiryData),
                'status' => $isPaid ? 1 : 0,
            ]);

            return [
                'success' => true,
                'is_paid' => $isPaid,
                'amount' => $inquiryData['amount_cents'] / 100,
                'customer_id' => $inquiryData['order']['merchant_order_id'] ?? null
            ];
        } catch (Exception $e) {
            Log::error('PayMob checkPaymentStatus Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
