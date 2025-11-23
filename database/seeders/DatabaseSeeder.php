<?php
namespace Database\Seeders;

use App\Models\OfflinePayments;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call([
             AdminRoleTable::class,
            //  AdminTable::class,
            //  SellerTableSeeder::class,
            FeatureDealSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PartnerSeeder::class,
            OfflinePaymentMethodsSeeder::class,
         ]);
    }
}
