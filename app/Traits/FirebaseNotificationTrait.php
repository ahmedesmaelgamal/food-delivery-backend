<?php

namespace App\Traits;

use App\Models\DeviceToken;
use App\Models\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

trait FirebaseNotificationTrait
{
    protected function fcmUrl()
    {
        return "https://fcm.googleapis.com/v1/projects/maxim-topbusiness/messages:send";
    }

    public function sendFcm($data, $user_ids = [],  $additionalData = [])
    {
        $additionalData = $data;
        $apiUrl = $this->fcmUrl();
        $accessToken = $this->getAccessToken();

        $deviceTokens = DeviceToken::query()->whereIn('user_id', $user_ids)->pluck('token')->toArray();
        // $deviceTokens = ["fKqQ_AhYSUqrNN2Qolrgv8:APA91bHqLzetn2LPcrNZl1Og5PYnZnwCvNCTgCanLAV0xU9BM_N5vy4GdwFalEtNqk7xHUhF_c5YZPJGepLsTjZnEMimlllF8lwJkDGXO7rixmpBTd1FT7M"];

        foreach ($user_ids as $user_id) {
            Notification::query()->create([
                'title' => $data['title'],
                'body' => $data['body'],
                'sent_to' => $user_id,
                'refrence_id' => isset($data['refrence_id']) ?? null,
                'refrence_type'=> isset($data['refrence_type']) ??    null,
            ]);
        }

        $responses = [];
        foreach ($deviceTokens as $token) {
            $payload = $this->preparePayload($data, $token, $additionalData);
            $responses[] = $this->sendNotification($apiUrl, $accessToken, $payload);
        }

        return response()->json(['responses' => $responses]);
    }

    protected function getAccessToken()
    {
        // Move this file outside public directory (e.g., storage/app/firebase.json)

        $credentialsFilePath = storage_path('app/firebase.json');

        if (!file_exists($credentialsFilePath)) {
            throw new \Exception('Firebase credentials file not found');
        }

        $credentials = json_decode(file_get_contents($credentialsFilePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in Firebase credentials');
        }

        $now = time();
        $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $jwtPayload = json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]);

        $jwtHeaderBase64 = $this->base64UrlEncode($jwtHeader);
        $jwtPayloadBase64 = $this->base64UrlEncode($jwtPayload);

        $signature = '';
        $privateKey = $credentials['private_key'];

        openssl_sign(
            "$jwtHeaderBase64.$jwtPayloadBase64",
            $signature,
            $privateKey,
            'sha256'
        );

        $jwtSignatureBase64 = $this->base64UrlEncode($signature);

        $jwt = "$jwtHeaderBase64.$jwtPayloadBase64.$jwtSignatureBase64";

        // Exchange JWT for access token
        $client = new Client();
        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $tokenData = json_decode($response->getBody(), true);

        return $tokenData['access_token'];
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function preparePayload($data, $token, $additionalData = [])
    {
        // Ensure additionalData is an array (not an object)
        if (is_object($additionalData) && method_exists($additionalData, 'toArray')) {
            $additionalData = $additionalData->toArray();
        } elseif (!is_array($additionalData)) {
            $additionalData = [];
        }

        // Ensure all values are strings (FCM requires string values)
        array_walk_recursive($additionalData, function (&$item) {
            $item = (string)$item;
        });

        // Fix reserved keywords (like 'from')
        if (isset($additionalData['from'])) {
            $additionalData['from_custom'] = $additionalData['from'];
            unset($additionalData['from']);
        }

        // Build the correct FCM payload structure
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $data['title'] ?? '',
                    'body' => $data['body'] ?? '',
                ],
               'data' => $additionalData ,
            ],
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    protected function sendNotification($url, $accessToken, $payload)
    {
        $client = new Client();

        try {
            $response = $client->post($url, [
                'headers' => [
                    "Authorization" => "Bearer " . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => $payload,
            ]);


            return json_decode($response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
