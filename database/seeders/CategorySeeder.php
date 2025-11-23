<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Main Categories
            [
                'name' => 'Electronics',
                'slug' => Str::slug('Electronics'),
                'icon' => 'electronics.png',
                'icon_storage_type' => 'public',
                'parent_id' => 0,
                'position' => 1,
                'home_status' => 1,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'slug' => Str::slug('Fashion'),
                'icon' => 'fashion.png',
                'icon_storage_type' => 'public',
                'parent_id' => 0,
                'position' => 2,
                'home_status' => 1,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Home & Garden',
                'slug' => Str::slug('Home & Garden'),
                'icon' => 'home-garden.png',
                'icon_storage_type' => 'public',
                'parent_id' => 0,
                'position' => 3,
                'home_status' => 1,
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Electronics Subcategories
            [
                'name' => 'Smartphones',
                'slug' => Str::slug('Smartphones'),
                'icon' => 'smartphones.png',
                'icon_storage_type' => 'public',
                'parent_id' => 1, // Electronics
                'position' => 1,
                'home_status' => 1,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Laptops',
                'slug' => Str::slug('Laptops'),
                'icon' => 'laptops.png',
                'icon_storage_type' => 'public',
                'parent_id' => 1, // Electronics
                'position' => 2,
                'home_status' => 1,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Headphones',
                'slug' => Str::slug('Headphones'),
                'icon' => 'headphones.png',
                'icon_storage_type' => 'public',
                'parent_id' => 1, // Electronics
                'position' => 3,
                'home_status' => 1,
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Fashion Subcategories
            [
                'name' => 'Men\'s Clothing',
                'slug' => Str::slug('Men\'s Clothing'),
                'icon' => 'mens-clothing.png',
                'icon_storage_type' => 'public',
                'parent_id' => 2, // Fashion
                'position' => 1,
                'home_status' => 1,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Women\'s Clothing',
                'slug' => Str::slug('Women\'s Clothing'),
                'icon' => 'womens-clothing.png',
                'icon_storage_type' => 'public',
                'parent_id' => 2, // Fashion
                'position' => 2,
                'home_status' => 1,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Accessories',
                'slug' => Str::slug('Accessories'),
                'icon' => 'accessories.png',
                'icon_storage_type' => 'public',
                'parent_id' => 2, // Fashion
                'position' => 3,
                'home_status' => 1,
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Home & Garden Subcategories
            [
                'name' => 'Furniture',
                'slug' => Str::slug('Furniture'),
                'icon' => 'furniture.png',
                'icon_storage_type' => 'public',
                'parent_id' => 3, // Home & Garden
                'position' => 1,
                'home_status' => 1,
                'priority' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kitchenware',
                'slug' => Str::slug('Kitchenware'),
                'icon' => 'kitchenware.png',
                'icon_storage_type' => 'public',
                'parent_id' => 3, // Home & Garden
                'position' => 2,
                'home_status' => 1,
                'priority' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Decoration',
                'slug' => Str::slug('Decoration'),
                'icon' => 'decoration.png',
                'icon_storage_type' => 'public',
                'parent_id' => 3, // Home & Garden
                'position' => 3,
                'home_status' => 1,
                'priority' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert($category);
        }
    }
}
