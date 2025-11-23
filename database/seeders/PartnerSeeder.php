<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    public function run()
    {
        $partners = [
            [
                'name' => 'Grourmet food stores',
                'image' => 'grourmet-food-stores.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Seoudi',
                'image' => 'seoudi.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Talabat',
                'image' => 'talabat.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'InstaShop',
            //     'image' => 'images/partners/.jpg',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'name' => 'Oscar grand stores',
                'image' => 'oscar-grand-stores.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Breadfast',
                'image' => 'breadfast.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spinneys',
                'image' => 'spinneys.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rabbit',
                'image' => 'rabbit.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Geant',
                'image' => 'geant.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zumra',
                'image' => 'zumra.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Garnell',
                'image' => 'garnell.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'The grocer',
            //     'image' => 'test1.jpg',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'name' => 'Fathallah',
                'image' => 'fathallah.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hyper 1',
                'image' => 'hyper-1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'lulu market',
            //     'image' => 'test1.jpg',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'name' => 'negmet heliopolis',
                'image' => 'negmet-heliopolis.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'royal house',
                'image' => 'royal-house.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($partners as $partner) {
            DB::table('partners')->insert($partner);
        }
    }
}
