<?php

namespace App\Http\Controllers\Wordpress\webhook;

use App\Http\Controllers\Controller;
use App\Models\Coupon;



class WordpressCouponController extends Controller
{
    public function handleCouponCreated(array $payload): void
    {
//        \Log::info('in wordpress coupon controller:       '.json_encode($payload));

        // Map WP payload into your local DB model
        Coupon::create([
            'wordpress_id'=>$payload['id'],
            'code' => $payload['code'],
            'discount' => $payload['amount'],
            'status'=>$payload['status']=='publish'?1:0,
            'usage_count' => $payload['usage_count'],
            'expire_date'=>$payload['date_expires'],
            'limit'=>$payload['usage_limit'],
            'user_count'=>$payload['usage_limit_per_user'],
            'title' => $payload['description'],
            'start_date'=>$payload['date_created'],
            // 'email_restrictions'=>$coupon['email_restrictions'],
            // 'used_by'=>$coupon['used_by'],
            'email_restrictions' => json_encode( $payload['email_restrictions'] ?? []),
            'used_by' => json_encode($payload['used_by'] ?? []),
            'discount_type'=>$payload['discount_type'],

        ]);
        \Log::info('coupon has been created upon update from wordpress through webhook');

    }


    public function handleCouponUpdated(array $payload): void
    {
        
        $coupon = Coupon::where('wordpress_id', $payload['id'])->first();
if ($coupon){
    $coupon->update(
        [
            'code' => $payload['code'],
            'discount' => $payload['amount'],
            'status'=>$payload['status']=='publish'?1:0,
            'usage_count' => $payload['usage_count'],
            'expire_date'=>$payload['date_expires'],
            'limit'=>$payload['usage_limit'],
            'user_count'=>$payload['usage_limit_per_user'],
            'title' => $payload['description'],
            'start_date'=>$payload['date_created'],
            // 'email_restrictions'=>$coupon['email_restrictions'],
            // 'used_by'=>$coupon['used_by'],
            'email_restrictions' => json_encode( $payload['email_restrictions'] ?? []),
            'used_by' => json_encode($payload['used_by'] ?? []),
            'discount_type'=>$payload['discount_type'],
        ]
    );
    \Log::info('coupon has been updated upon update from wordpress through webhook');
}

    }

    public function handleCouponDeleted(array $payload): void
    {
        Coupon::where('wordpress_id', $payload['id'])->delete();
        \Log::info('coupon has been deleted upon update from wordpress through webhook');

    }
}
