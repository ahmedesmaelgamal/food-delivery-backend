<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeatureDealSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $feature_deals = [
            // Homepage feature-deals
            [
                'url' => '/products/summer-sale',
                'photo' => 'feature-deal/test1.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/categories/electronics',
                'photo' => 'feature-deal/test2.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/products/new-arrivals',
                'photo' => 'feature-deal/test3.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/promotions/flash-sale',
                'photo' => 'feature-deal/test4.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/categories/fashion',
                'photo' => 'feature-deal/test5.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Seasonal Promotions
            [
                'url' => '/winter-collection',
                'photo' => 'feature-deal/test5.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/back-to-school',
                'photo' => 'feature-deal/test6.webp',
                'status' => 0, // Inactive
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/holiday-special',
                'photo' => 'feature-deal/test7.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Product Category feature-deal
            [
                'url' => '/categories/smartphones',
                'photo' => 'feature-deal/test8.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/categories/laptops',
                'photo' => 'feature-deal/test9.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/categories/home-appliances',
                'photo' => 'feature-deal/test10.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/categories/beauty',
                'photo' => 'feature-deal/test11.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Special Offers
            [
                'url' => '/offers/free-shipping',
                'photo' => 'feature-deal/test12.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/offers/buy-one-get-one',
                'photo' => 'feature-deal/test5.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/offers/clearance-sale',
                'photo' => 'feature-deal/test4.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Brand Promotions
            [
                'url' => '/brands/apple',
                'photo' => 'feature-deal/test3.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/brands/samsung',
                'photo' => 'feature-deal/test2.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => '/brands/nike',
                'photo' => 'feature-deal/test4.webp',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Inactive feature-deal (Examples)
            [
                'url' => '/expired-promo',
                'photo' => 'feature-deal/test1.webp',
                'status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'url' => null, // Example with no URL
                'photo' => 'feature-deal/test1.webp',
                'status' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('feature_deals')->insert($feature_deals);
    }
}
