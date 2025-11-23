<?php

namespace App\Http\Controllers\Wordpress\webhook;

use App\Http\Controllers\Controller;
use App\Models\User;


class WordpressCustomerController extends Controller
{
    public function handleCustomerCreated(array $payload): void
    {
        \Log::info('Customer has been created upon receiving an update from webhook', $payload);

        User::create(
            [
                'wordpress_id'=>$payload['id'],
                'email' => $payload['email'],
                'name' => $payload['username'],
                'f_name' => $payload['first_name'],
                'l_name' => $payload['last_name'],
                'phone' => $payload['billing']['phone'],
                'image' => $payload['avatar_url'],
                'country' => $payload['billing']['country'],
                'city' => $payload['billing']['city'],
                'zip' => $payload['billing']['postcode'],
                'street_address' => $payload['billing']['address_1'],
                'house_no' => $payload['billing']['address_2'],
                'apartment_no' => '',
                'is_active' => 1,
                'payment_card_last_four' => '',
                'payment_card_brand' => '',
                'payment_card_fawry_token' => '',
                'login_medium' => '',
                'social_id' => '',
                'social_type' => '',
                'is_phone_verified' => 0,
                'temporary_token' => '',
                'is_email_verified' => now()->timestamp,
                'wallet_balance' => 0.00,
                'loyalty_point' => 0.0000,
                'login_hit_count' => 0,
                'is_temp_blocked' => 0,
                'temp_block_time' => null,
                'referral_code' => '',
                'referred_by' => 0,
                'app_language' => 'en'
            ]
        );

    }

    public function handleCustomerUpdated(array $payload): void
    {
        \Log::info('Customer has been updated upon receiving an update from webhook', $payload);

        $user=User::where('wordpress_id', $payload['id'])->first();
        if ($user) {
            $user->update(
                [
                    'email' => $payload['email'],
                    'name' => $payload['username'],
                    'f_name' => $payload['first_name'],
                    'l_name' => $payload['last_name'],
                    'phone' => $payload['billing']['phone'],
                    'image' => $payload['avatar_url'],
                    'country' => $payload['billing']['country'],
                    'city' => $payload['billing']['city'],
                    'zip' => $payload['billing']['postcode'],
                    'street_address' => $payload['billing']['address_1'],
                    'house_no' => $payload['billing']['address_2'],
                    'apartment_no' => '',
                    'is_active' => 1,
                    'payment_card_last_four' => '',
                    'payment_card_brand' => '',
                    'payment_card_fawry_token' => '',
                    'login_medium' => '',
                    'social_id' => '',
                    'social_type' => '',
                    'is_phone_verified' => 0,
                    'temporary_token' => '',
                    'is_email_verified' => now()->timestamp,
                    'wallet_balance' => 0.00,
                    'loyalty_point' => 0.0000,
                    'login_hit_count' => 0,
                    'is_temp_blocked' => 0,
                    'temp_block_time' => null,
                    'referral_code' => '',
                    'referred_by' => 0,
                    'app_language' => 'en',
                ]
            );
        }

    }

    public function handleCustomerDeleted(array $payload): void
    {
        \Log::info('customer has been deleted upon update from wordpress through webhook');
        User::where('wordpress_id', $payload['id'])->delete();
    }
}
