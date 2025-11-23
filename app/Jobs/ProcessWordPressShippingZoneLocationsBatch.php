<?php

namespace App\Jobs;

use App\Models\DigitalProductVariation;
use App\Models\ShippingZoneLocation;
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

class ProcessWordPressShippingZoneLocationsBatch implements ShouldQueue
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
                $this->syncShippingZoneLocations($shippingZoneId);
            }

            if ($this->isLastBatch) {
                $this->syncDeletedShippingZoneLocations();
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
        // )->timeout(60)->get('https://maximfood.com/wp-json/wc/v3/products', [
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
                    ->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/locations");

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

    private function syncShippingZoneLocations($shippingZoneId)
    {
//        dd($shippingZoneId);
        $page = 1;
        $perPage = 100;
        $hasMore = true;

        while ($hasMore) {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/locations", [
                'per_page' => $perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'desc'
            ]);

            if ($response->successful()) {
                $shippingZoneLocations = $response->json();

                Log::info("Processing shipping zone locations for shipping zone", [
                    'shipping_zone_id' => $shippingZoneId,
                    'page' => $page,
                    'shipping_zone_locations_count' => count($shippingZoneLocations)
                ]);

                foreach ($shippingZoneLocations as $shippingZoneLocation) {
//                    try {
                        $shippingZoneLocationRecord = ShippingZoneLocation::create(
//                            ['wordpress_id' => $shippingZoneLocation['id']],
                            [
                                'code' => preg_replace('/.*:/', '', $shippingZoneLocation['code']),
                                'type' => $shippingZoneLocation['type'],
                                'shipping_zone_id' => strpos($shippingZoneLocation['code'], 'EG:') === 0 ? 2 : 1,
//                              'shipping_zone_id' => $shippingZoneId,
                                'is_notified' => true,
                            ]
                        );
//                    if ($shippingZoneLocation && ShippingZoneLocation::where('wordpress_id', $shippingZoneLocation['id'])->first()->is_notified==false) {
//                        $additionalData = [
//                            'refrence_id' => $shippingZoneLocationRecord->id,
//                            'refrence_type'=>'shipping_zone_location',
//                        ];
//                        $data = [
//                            "title" => request('title', "new shipping zone location has been added" ),
//                            "body" => request('body', "shipping zone location has been added"),
//                        ];
//                        $user_ids = User::pluck('id')->toArray();
//                        // $this->sendFcm($data, $user_ids, $additionalData);
//
//                        ShippingZoneLocation::where('wordpress_id', $shippingZoneLocation['id'])->update(['is_notified' => true]);
//                        //make sure to get all the shipping zone location for the first time before uncommenting this section of code
//                    }

//                        Log::info("Processed shipping zone location", [
////                            'shipping_zone_location_id' => $shippingZoneLocation['id'],
//                            'shipping_zone_id' => $shippingZoneId
//                        ]);

//                    } catch (\Exception $e) {
//                        Log::error("shipping zone location sync failed", [
//                            'shipping_zone_id' => $shippingZoneId,
//                            'shipping_zone_location_id' => $shippingZoneLocation['id'],
//                            'error' => $e->getMessage()
//                        ]);
//                    }
                }

                $hasMore = count($shippingZoneLocations) === $perPage;
                $page++;
            } else {
                Log::error("Failed to fetch shipping zone locations for shipping zone", [
                    'shipping_zone_id' => $shippingZoneId,
                    'page' => $page,
                    'status' => $response->status()
                ]);
                $hasMore = false;
            }
        }
    }

    private function syncDeletedShippingZoneLocations()
    {
//        try {
            Log::info("Starting deleted shipping zones sync in last batch");

            $wordpressShippingZoneLocationIds = $this->getAllWordPressShippingZoneLocationIds();

            if (empty($wordpressShippingZoneLocationIds)) {
                Log::warning("No WordPress shipping zone location IDs found for deletion sync");
                return;
            }

            $localShippingZoneLocations = ShippingZoneLocation::select('id', 'wordpress_id')->get();
            $deletedCount = 0;

            foreach ($localShippingZoneLocations as $localShippingZoneLocation) {
                if (!in_array($localShippingZoneLocation->wordpress_id, $wordpressShippingZoneLocationIds)) {
                    DB::beginTransaction();

                    try {
//                        ProductStock::where('product_id', $localShippingZoneLocation->id)->delete();

                        $localShippingZoneLocation->delete();

                        DB::commit();
                        $deletedCount++;

                        Log::info("Deleted shipping zone", [
                            'wordpress_id' => $localShippingZoneLocation->wordpress_id,
                            'local_id' => $localShippingZoneLocation->id
                        ]);

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to delete shipping zone", [
                            'wordpress_id' => $localShippingZoneLocation->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted shipping zone locations sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_shipping_zone_locations' => count($wordpressShippingZoneLocationIds),
                'total_local_shipping_zone_locations' => $localShippingZoneLocations->count()
            ]);

//        } catch (\Exception $e) {
//            Log::error("Deleted shipping zone locations sync failed", ['error' => $e->getMessage()]);
//        }
    }

    private function getAllWordPressShippingZoneLocationIds()
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
                )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL')."/wp-json/wc/v3/shipping/zones/{$shippingZoneId}/locations", [
                    'per_page' => $perPage,
                    'page' => $page,
                    '_fields' => 'id'
                ]);

                if ($response->successful()) {
                    $shippingZoneLocations = $response->json();

                    foreach ($shippingZoneLocations as $index => $shippingZoneLocation) {
                        $allIds[] = $index;

                    }
                    $hasMore = count($shippingZoneLocations) === $perPage;
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
