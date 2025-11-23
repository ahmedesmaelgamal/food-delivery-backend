<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Traits\FirebaseNotificationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Claims\Custom;

class ProcessWordPressCustomersBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels , FirebaseNotificationTrait;


    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
        try {
            Log::info(message: "handle in syncCustomers before sync");
            // مزامنة المنتجات العادية
            $this->syncCustomers();
            Log::info(message: "handle in syncCustomers after sync");

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedCustomers();
            }

        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncCustomers()
    {
        Log::info("in syncCustomers");
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/customers', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            'orderby' => 'name',
            'order' => 'desc'
        ]);
        Log::info($response->successful());

        if ($response->successful()) {
            $customers = $response->json();

            Log::info("Processing batch", [
                'page' => $this->page,
                'customers_count' => count($customers),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($customers as $customer) {


                try {
                Log::info("before updateOrCreate");
                    $user=User::updateOrCreate(
                        ['wordpress_id' => $customer['id']],
                        [
                            'email' => $customer['email'],
                            'name' => $customer['username'],
                            'f_name' => $customer['first_name'],
                            'l_name' => $customer['last_name'],
                            'phone' => $customer['billing']['phone'],
                            'image' => $customer['avatar_url'],
                            'country' => $customer['billing']['country'],
                            'city' => $customer['billing']['city'],
                            'zip' => $customer['billing']['postcode'],
                            'street_address' => $customer['billing']['address_1'],
                            'house_no' => $customer['billing']['address_2'],
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





                } catch (\Exception $e) {
                    Log::error("customer sync failed in batch", [
                        'page' => $this->page,
                        'customer_id' => $customer['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_customers' => count($customers)
            ]);
        }
    }


    private function syncDeletedCustomers()
    {
        try {
            Log::info("Starting deleted customers sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressCustomerIds = $this->getAllWordPressCustomerIds();

            if (empty($wordpressCustomerIds)) {
                Log::warning("No WordPress customer IDs found for deletion sync");
                return;
            }

            // جلب المنتجات المحلية
            $localCustomers = User::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localCustomers as $localCustomer) {
                if (!in_array(  $localCustomer->wordpress_id, $wordpressCustomerIds)) {
                    DB::beginTransaction();

                    try {

                        $localCustomer->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted customer", [
                            'wordpress_id' => $localCustomer->wordpress_id,
                            'name' => $localCustomer->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete customer", [
                            'wordpress_id' => $localCustomer->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted customers sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_customers' => count($wordpressCustomerIds),
                'total_local_customers' => $localCustomers->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted customers sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressCustomerIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/customers', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $customers = $response->json();

                foreach ($customers as $customer) {
                    $allIds[] = $customer['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress customers for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($customers) == 100);

        return $allIds;
    }
}
