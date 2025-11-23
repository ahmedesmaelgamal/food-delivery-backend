<?php

namespace App\Jobs;

// use App\Enums\ExportFileNames\Admin\Category;
use App\Models\OrderDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FirebaseNotificationTrait;

class ProcessWordPressOrdersBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels , FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {

        Log::info('before syncOrders', [
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
        Log::info('ProcessWordPressOrdersBatch job created');
        try {
            Log::info('hello');
            // مزامنة المنتجات العادية
            $this->syncOrders();
            Log::info('after syncOrders', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
                $this->syncDeletedOrders();
            }


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncOrders()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/orders', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            'orderby' => 'date',
            'order' => 'desc'
        ]);

        $orders = $response->json();
        if (!empty($orders)) {
            $firstOrderId = $orders[0]['id'];

            Log::info("First order ID from API", [
                'firstOrderId' => $firstOrderId
            ]);

            try {
                $order = Order::where('wordpress_id', $firstOrderId)->first();
                Log::info("Order lookup result", [
                    'exists_in_db' => $order ? true : false,
                    'order' => $order
                ]);
            } catch (\Exception $e) {
                Log::error("Order query failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if ($response->successful()) {
            Log::info("Processing batch", [
                'page' => $this->page,
                'orders_count' => count($orders),
                'per_page' => $this->perPage
            ]);

            foreach ($orders as $orderData) {
                DB::beginTransaction();
                try {
                    $orderRecord = Order::updateOrCreate(
                        ['wordpress_id' => $orderData['id']],
                        [
                            'wordpress_id' => $orderData['id'],
                            'customer_id' => $orderData['customer_id'] ?? null,
                            'payment_status' => $this->mapPaymentStatus($orderData),
                            'order_status' => $this->mapOrderStatus($orderData),
                            'payment_method' => $orderData['payment_method'] ?? null,
                            'order_amount' => (float)$orderData['total'],
                            'discount_amount' => (float)$orderData['discount_total'],
                            'shipping_cost' => (float)$orderData['shipping_total'],
                            'created_at' => $orderData['date_created'],
                            'updated_at' => $orderData['date_modified'],
                            'shipping_address' => json_encode($orderData['shipping']),
                            'billing_address_data' => json_encode($orderData['billing']),
                            'order_note' => $orderData['customer_note'] ?? null,
                            'transaction_ref' => $orderData['transaction_id'] ?? null,
                            'coupon_code' => $this->extractCouponCode($orderData),
                            'is_notified' => false
                        ]
                    );

                    $this->syncOrderDetails($orderRecord->id, $orderData);

                    if (!$orderRecord->is_notified) {
                        $additionalData = [
                            'refrence_id' => $orderRecord->id,
                            'refrence_type' => 'order',
                        ];
                        $data = [
                            "title" => "New Order Received",
                            "body" => "Order #{$orderData['id']} has been created",
                        ];
                        $user_ids = User::pluck('id')->toArray();
                        $orderRecord->update(['is_notified' => true]);
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Order sync failed", [
                        'order_id' => $orderData['id'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info("Batch completed", [
                'page' => $this->page,
                'processed_orders' => count($orders)
            ]);
        } else {
            Log::error("API request failed", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        }
    }

// Helper methods
    private function mapPaymentStatus($orderData)
    {
        if ($orderData['date_paid']) {
            return 'paid';
        }
        return $orderData['status'] === 'pending' ? 'unpaid' : 'pending';
    }

    private function mapOrderStatus($orderData)
    {
        $statusMap = [
            'pending' => 'pending',
            'processing' => 'confirmed',
            'on-hold' => 'confirmed',
            'completed' => 'delivered',
            'cancelled' => 'canceled',
            'refunded' => 'returned'
        ];

        return $statusMap[$orderData['status']] ?? 'pending';
    }

    private function extractCouponCode($orderData)
    {
        if (!empty($orderData['coupon_lines'])) {
            return $orderData['coupon_lines'][0]['code'] ?? null;
        }
        return null;
    }

    private function syncOrderDetails($orderId, $orderData)
    {
        foreach ($orderData['line_items'] as $item) {
            Log::info('order details');
            OrderDetail::updateOrCreate(
                [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id']
                ],
                [
                    'qty' => $item['quantity'],
                    'price' => (float) $item['price'],
                    'discount' => (float) ($item['subtotal'] - $item['total']),
                    'product_details' => json_encode([
                        'name' => $item['name'],
                        'sku' => $item['sku'],
                        'image' => $item['image']['src'] ?? null
                    ]),
                    'created_at' => $orderData['date_created'],
                    'updated_at' => $orderData['date_modified']
                ]
            );
        }
    }
    private function syncDeletedOrders()
    {
        try {
            Log::info("Starting deleted orders sync in last batch");

            $wordpressOrderIds = $this->getAllWordPressOrderIds();

            if (empty($wordpressOrderIds)) {
                Log::warning("No WordPress order IDs found for deletion sync");
                return;
            }

            $localOrders = Order::select('id', 'wordpress_id')->get();

            $deletedCount = 0;

            foreach ($localOrders as $localOrder) {
                if (!in_array($localOrder->wordpress_id, $wordpressOrderIds)) {
                    DB::beginTransaction();

                    try {
                        Product::where('order_id', $localOrder->id)->delete();

                        // حذف الفئة
                        $localOrder->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted order and associated products", [
                            'wordpress_id' => $localOrder->wordpress_id,
                            'name' => $localOrder->name
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete order and associated products", [
                            'wordpress_id' => $localOrder->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted orders sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_orders' => count($wordpressOrderIds),
                'total_local_orders' => $localOrders->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted orders sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressOrderIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wp-json/wc/v3/orders', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $orders = $response->json();

                foreach ($orders as $order) {
                    $allIds[] = $order['id'];
                }

                $page++;

                usleep(300000);
            } else {
                Log::error("Failed to fetch WordPress orders for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($orders) == 100);

        return $allIds;
    }
}
