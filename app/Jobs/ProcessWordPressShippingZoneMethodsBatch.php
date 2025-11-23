<?php

namespace App\Jobs;

use App\Models\DigitalProductVariation;
use App\Models\ShippingZoneMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FirebaseNotificationTrait;

class ProcessWordPressShippingZoneMethodsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FirebaseNotificationTrait;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch;
    public $shippingZoneIds;

    public function __construct(array $shippingZoneIds,$page, $perPage = 10, $isLastBatch = false)
    {
        $this->shippingZoneIds = $shippingZoneIds;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;
    }

    public function handle()
    {
        try {
            foreach ($this->shippingZoneIds as $shippingZoneId) {
                $this->syncShippingZoneMethods($shippingZoneId);
            }

            if ($this->isLastBatch) {
                $this->syncDeletedShippingZoneMethods();
            }

        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getWordPressShippingZonesBatch(array $shippingZoneIds)
    {
        // $response = Http::withBasicAuth(
        //     env('CONSUMER_KEY'),
        //     env('CONSUMER_SECRET')
        // )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
        //     'per_page' => $this->perPage,
        //     'page' => $this->page,
        //     'orderby' => 'date',
        //     'order' => 'desc'
        // ]);

        // if (!$response->successful()) {
        //     Log::error("Failed to fetch WordPress products batch", [
        //         'page' => $this->page,
        //         'status' => $response->status(),
        //         'response' => $response->body()
        //     ]);
        //     return [];
        // }

        // return $response->json();



            $successfulIds = [];
            $failedIds = [];

            foreach ($shippingZoneIds as $shippingZoneId) {
                try {
                    $response = Http::withBasicAuth(
                        env('CONSUMER_KEY'),
                        env('CONSUMER_SECRET')
                    )
                    ->timeout(30)
                    ->retry(3, 1000) // Retry 3 times with 1 second delay
                    ->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/methods");

                    if ($response->successful()) {
                        $successfulIds[$shippingZoneId] = $response->json();
                    } else {
                        $failedIds[$shippingZoneId] = $response->status();

                        // Log specific error details
                        Log::warning("Failed to fetch shipping zone", [
                            'shipping_zone_id' => $shippingZoneId,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);

                        // Implement exponential backoff
                        sleep(min(pow(2, count($failedIds)), 60)); // Max 60 seconds
                    }
                } catch (\Exception $e) {
                    $failedIds[$shippingZoneId] = $e->getMessage();
                    Log::error("Exception fetching shipping zone", [
                        'shipping_zone_id' => $shippingZoneId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return [
                'successful' => $successfulIds,
                'failed' => $failedIds
            ];
    }

    private function syncShippingZoneMethods($shippingZoneId)
    {
//        dd($shippingZoneId);
        $page = 1;
        $perPage = 100;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/methods", [
                'per_page' => $perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->successful()) {
                $shippingZoneMethods = $response->json();
//                dd($shippingZoneMethods);

                Log::info("Processing shipping zone methods for shipping zone", [
                    'shipping_zone_id' => $shippingZoneId,
                    'page' => $page,
                    'shipping_zone_methods_count' => count($shippingZoneMethods)
                ]);

                foreach ($shippingZoneMethods as $shippingZoneMethod) {
//                    dd($shippingZoneMethod['settings']['cost']['value'],$shippingZoneMethod['method_description'],$shippingZoneMethod['method_id']);

//                    try {
                        $shippingZoneMethodRecord = ShippingZoneMethod::updateOrCreate(
                            ['wordpress_id' => $shippingZoneMethod['id']],
                            [
                                'shipping_zone_id' => $shippingZoneId,
                                'method_id' => isset($shippingZoneMethod['method_id'])?$shippingZoneMethod['method_id']: null,
                                'method_description' => (string) isset($shippingZoneMethod['method_description']) ? $shippingZoneMethod['method_description'] : null,
                                'min_amount' => ((float)isset($shippingZoneMethod['settings']['cost']['value'])?$shippingZoneMethod['settings']['cost']['value']:(isset($shippingZoneMethod['settings']['min_amount']['value'])?$shippingZoneMethod['settings']['min_amount']['value']: null)),
                                'ignore_discounts_value' => isset($shippingZoneMethod['settings']['ignore_discounts']['value']) ?( $shippingZoneMethod['settings']['ignore_discounts']['value']=='no'?0:1): 0,
                                'enabled'=>isset($shippingZoneMethod['enabled'])?($shippingZoneMethod['enabled']== 'true'?1:0): 0,
                                'is_notified' => false,
                            ]
                        );
                        Log::info($shippingZoneMethodRecord);
//                    if ($shippingZoneMethod && ShippingZoneMethod::where('wordpress_id', $shippingZoneMethod['id'])->first()->is_notified==false) {
//                        $additionalData = [
//                            'refrence_id' => $shippingZoneMethodRecord->id,
//                            'refrence_type'=>'shipping_zone_method',
//                        ];
//                        $data = [
//                            "title" => request('title', "new shipping zone method has been added" ),
//                            "body" => request('body', "shipping zone method has been added"),
//                        ];
//                        $user_ids = User::pluck('id')->toArray();
//                        // $this->sendFcm($data, $user_ids, $additionalData);
//
//                        ShippingZoneMethod::where('wordpress_id', $shippingZoneMethod['id'])->update(['is_notified' => true]);
//                        //make sure to get all the shipping zone method for the first time before uncommenting this section of code
//                    }

//                        Log::info("Processed shipping zone method", [
////                            'shipping_zone_method_id' => $shippingZoneMethod['id'],
//                            'shipping_zone_id' => $shippingZoneId
//                        ]);

//                    } catch (\Exception $e) {
//                        Log::error("shipping zone method sync failed", [
//                            'shipping_zone_id' => $shippingZoneId,
//                            'shipping_zone_method_id' => $shippingZoneMethod['id'],
//                            'error' => $e->getMessage()
//                        ]);
//                    }
                }

                $hasMore = count($shippingZoneMethods) === $perPage;
                $page++;
            } else {
                Log::error("Failed to fetch shipping zone methods for shipping zone", [
                    'shipping_zone_id' => $shippingZoneId,
                    'page' => $page,
                    'status' => $response->status()
                ]);
                $hasMore = false;
            }
        }
    }

    private function syncDeletedShippingZoneMethods()
    {
        try {
            Log::info("Starting deleted shipping zones sync in last batch");

            $wordpressShippingZoneMethodIds = $this->getAllWordPressShippingZoneMethodIds();

            if (empty($wordpressShippingZoneMethodIds)) {
                Log::warning("No WordPress shipping zone method IDs found for deletion sync");
                return;
            }

            $localShippingZoneMethods = ShippingZoneMethod::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localShippingZoneMethods as $localShippingZoneMethod) {
                if (!in_array($localShippingZoneMethod->wordpress_id, $wordpressShippingZoneMethodIds)) {
                    DB::beginTransaction();

                    try {
//                        ProductStock::where('product_id', $localShippingZoneMethod->id)->delete();

                        $localShippingZoneMethod->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted shipping zone", [
                            'wordpress_id' => $localShippingZoneMethod->wordpress_id,
                            'local_id' => $localShippingZoneMethod->id
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete shipping zone", [
                            'wordpress_id' => $localShippingZoneMethod->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted shipping zone methods sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_shipping_zone_methods' => count($wordpressShippingZoneMethodIds),
                'total_local_shipping_zone_methods' => $localShippingZoneMethods->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted shipping zone methods sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressShippingZoneMethodIds()
    {
//        $allIds = [];
        $page = 1;
        $perPage = 100;

        $shippingZoneIds = $this->getAllWordPressShippingZoneIds();

        foreach ($shippingZoneIds as $shippingZoneId) {
            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                $response = Http::withBasicAuth(
                    env('CONSUMER_KEY'),
                    env('CONSUMER_SECRET')
                )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/methods", [
                    'per_page' => $perPage,
                    'page' => $page,
                    '_fields' => 'id'
                ]);

                if ($response->successful()) {
                    $shippingZoneMethods = $response->json();

                    foreach ($shippingZoneMethods as $index => $shippingZoneMethod) {
                        $allIds[] = $index;
                    }

                    $hasMore = count($shippingZoneMethod) === $perPage;
                    $page++;

                    usleep(300000); // 0.3 seconds
                } else {
                    Log::error("Failed to fetch WordPress shipping zones", [
                        'shipping_zone_id' => $shippingZoneId,
                        'page' => $page
                    ]);
                    $hasMore = false;
                }
            }
        }

        return $allIds;
    }

    private function getAllWordPressShippingZoneIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/shipping/zones', [
                'per_page' => $perPage,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $shippingZones = $response->json();

                foreach ($shippingZones as $shippingZone) {
                    $allIds[] = $shippingZone['id'];
                }

                $page++;

                usleep(300000); // 0.3 seconds
            } else {
                Log::error("Failed to fetch WordPress shipping zones for ID sync", ['page' => $page]);
                break;
            }

        } while (count($shippingZones) === $perPage);

        return $allIds;
    }
}
