<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class OfflinePaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $offlinePaymentMethods = [
            [
                'method_name' => 'instapay',
                'number' => '01132456765',
                'link' => 'https://ipn.eg/S/mahmoud.amer8/instapay/7WxenO',
                // 'method_fields' => json_encode([]),
                // 'method_informations' => json_encode([]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'method_name' => 'vodafone_cash',
                'number' => '01033337653',
                'link' => null,
                // 'method_fields' => json_encode([]),
                // 'method_informations' => json_encode([]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'method_name' => 'etisalat_cash',
                'number' => '01199456665',
                'link' => null,
                // 'method_fields' => json_encode([]),
                // 'method_informations' => json_encode([]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'method_name' => 'orange_cash',
                'number' => '01232456765',
                'link' => null,
                // 'method_fields' => json_encode([]),
                // 'method_informations' => json_encode([]),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ];

        foreach ($offlinePaymentMethods as $offlinePaymentMethod) {
            DB::table('offline_payment_methods')->insert($offlinePaymentMethod);
        }
    }
}
