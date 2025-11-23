<?php

namespace App\Jobs;

// use App\Enums\ExportFileNames\Admin\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FirebaseNotificationTrait;

class ProcessWordPressCouponsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels , FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        Log::info('before syncCoupons', [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'isLastBatch' => $this->isLastBatch
        ]);

        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
        Log::info('ProcessWordPressCouponsBatch job created');
        try {
            // مزامنة المنتجات العادية
            $this->syncCoupons();
            // Log::info('after syncCoupons', [
            //     'page' => $this->page,
            //     'perPage' => $this->perPage,
            //     'isLastBatch' => $this->isLastBatch
            // ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedCoupons();
            }


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncCoupons()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/coupons', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            // 'orderby' => 'date',
            // 'order' => 'desc'
        ]);

        Log::info($response->getBody()->getContents());

        if ($response->successful()) {
            $coupons = $response->json();

            Log::info("Processing batch", [
                'page' => $this->page,
                'coupons_count' => count($coupons),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($coupons as $coupon) {
                DB::beginTransaction();

                try {
                    $couponRecord = Coupon::updateOrCreate(
                        ['wordpress_id' => $coupon['id']],
                        [
                            'code' => $coupon['code'],
                            'discount' => $coupon['amount'],
                            'excluded_product_ids' => json_encode($coupon['excluded_product_ids'] ?? []),
                            'excluded_product_categories' => json_encode($coupon['excluded_product_categories'] ?? []),
                            'product_ids'=>json_encode($coupon['product_ids'] ?? []),
                            'product_categories' => json_encode($coupon['product_categories'] ?? []),
                            'exclude_sale_items' => (boolean)$coupon['exclude_sale_items'],
                            'product_brands' => json_encode($coupon['product_brands'] ?? []),
                            'exclude_product_brands' => json_encode($coupon['exclude_product_brands'] ?? []),
                            'exclude_selected_products' => (boolean)$coupon['excluded_product_ids'],
                            'minimum_amount' => $coupon['minimum_amount'],
                            'maximum_amount' => $coupon['maximum_amount'],


                            'is_free_shipping' => (boolean)$coupon['free_shipping'],
                            'discount_type' => $coupon['discount_type'],
                            'limit_usage_to_x_items' => $coupon['limit_usage_to_x_items'],
                            'usage_limit_per_user' => $coupon['usage_limit_per_user'],
                            'status'=>$coupon['status']=='publish'?1:0,
                            'usage_count' => $coupon['usage_count'],
                            'expire_date'=>$coupon['date_expires'],
                            'limit'=>$coupon['usage_limit'],
                            'user_count'=>$coupon['usage_limit_per_user'],
                            'title' => $coupon['description'],
                            'start_date'=>$coupon['date_created'],
                            // 'email_restrictions'=>$coupon['email_restrictions'],
                            // 'used_by'=>$coupon['used_by'],
                            'email_restrictions' => json_encode( $coupon['email_restrictions'] ?? []),
                            'used_by' => json_encode($coupon['used_by'] ?? []),

                        ]
                    );


//                    if ($coupon && Coupon::where('wordpress_id', $coupon['id'])->first()->is_notified == false) {
//                        $additionalData = [
//                            'refrence_id' => $couponRecord->id,
//                            'refrence_type' => 'coupon',
//                        ];
//                        $data = [
//                            "title" => request('title', "new coupon has been added"),
//                            "body" => request('body', "coupon has been added"),
//                        ];
//                        $user_ids = User::pluck('id')->toArray();
//                        // $this->sendFcm($data, $user_ids, $additionalData);
//                        Coupon::where('wordpress_id', $coupon['id'])->update(['is_notified' => true]);
//                        //make sure to get all the coupons for the first time before uncommenting this section of code
//                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Coupon sync failed in batch", [
                        'page' => $this->page,
                        'coupon_id' => $coupon['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_coupons' => count($coupons) // تعديل هنا
            ]);
        }
    }

    private function syncDeletedCoupons()
    {
        try {
            Log::info("Starting deleted coupons sync in last batch");

            // جلب جميع IDs من WordPress
            $wordpressCouponIds = $this->getAllWordPressCouponIds();

            if (empty($wordpressCouponIds)) {
                Log::warning("No WordPress coupon IDs found for deletion sync");
                return;
            }

            // جلب الفئات المحلية
            $localCoupons = Coupon::select('id', 'wordpress_id')->get();

            $deletedCount = 0;

            foreach ($localCoupons as $localCoupon) {
                // إذا لم تعد الفئة موجودة في WordPress
                if (!in_array($localCoupon->wordpress_id, $wordpressCouponIds)) {
                    DB::beginTransaction();

                    try {
                        // حذف المنتجات المرتبطة أولاً
                        Product::where('coupon_id', $localCoupon->id)->delete();

                        // حذف الفئة
                        $localCoupon->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted coupon and associated products", [
                            'wordpress_id' => $localCoupon->wordpress_id,
                            'name' => $localCoupon->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete coupon and associated products", [
                            'wordpress_id' => $localCoupon->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Log::info("Deleted coupons sync completed", [
            //     'deleted_count' => $deletedCount,
            //     'total_wordpress_coupons' => count($wordpressCategoryIds),
            //     'total_local_coupons' => $localCoupons->count()
            // ]);

        } catch (\Exception $e) {
            Log::error("Deleted Coupons sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressCouponIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/coupons', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $coupons = $response->json();

                foreach ($coupons as $coupon) {
                    $allIds[] = $coupon['id'];
                }

                $page++;

                // تأخير قصير بين الطلبات
                usleep(300000); // 0.3 ثانية
            } else {
                Log::error("Failed to fetch WordPress coupons for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($coupons) == 100);

        return $allIds;
    }
}
