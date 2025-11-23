<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessWordPressCategoriesBatch;
use App\Jobs\ProcessWordpressCategoriesInArabicBatch;
use App\Jobs\ProcessWordPressCitiesBatch;
use App\Jobs\ProcessWordPressProductBuyItTogetherBatch;
use App\Jobs\ProcessWordPressPartnersBatch;
use App\Jobs\ProcessWordPressRatesBatch;
use App\Jobs\ProcessWordPressShippingZonesBatch;
use App\Jobs\ProcessWordPressShippingZoneLocationsBatch;
use App\Jobs\ProcessWordPressShippingZoneMethodsBatch;
use App\Jobs\ProcessWordpressProductVariantsBatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncWordPressData extends Command
{
    protected $signature = 'wordpress:sync {type : The type of data to sync}';

    protected $description = 'Sync WordPress data in batches
    
Available types:
  categories-en          - English categories
  categories-ar          - Arabic categories
  cities                 - Countries, states, and cities
  buy-it-together        - Buy it together products
  partners               - Partner images
  reviews                - Product reviews/ratings
  shipping-zones         - Shipping zones
  shipping-zone-locations - Shipping zone locations
  shipping-zone-methods  - Shipping zone methods';

    public function handle()
    {
        $type = $this->argument('type');

        $this->info("Starting WordPress {$type} sync...");
        Log::info("WordPress {$type} sync started via scheduler");

        try {
            switch ($type) {
                case 'variations':
                    return $this->syncCategories('en', ProcessWordpressProductVariantsBatch::class);

                case 'categories-en':
                    return $this->syncCategories('en', ProcessWordPressCategoriesBatch::class);

                case 'categories-ar':
                    return $this->syncCategories('ar', ProcessWordpressCategoriesInArabicBatch::class);

                case 'cities':
                    return $this->syncCities();

                case 'buy-it-together':
                    return $this->syncBuyItTogether();

                case 'partners':
                    return $this->syncPartners();

                case 'banners':
                    return $this->syncBanners();

                case 'reviews':
                    return $this->syncReviews();

                case 'shipping-zones':
                    return $this->syncShippingZones();

                case 'shipping-zone-locations':
                    return $this->syncShippingZoneLocations();

                case 'shipping-zone-methods':
                    return $this->syncShippingZoneMethods();

                default:
                    $this->error("Unknown sync type: {$type}");
                    $this->line("\nAvailable types:");
                    $this->line("  - categories-en");
                    $this->line("  - categories-ar");
                    $this->line("  - cities");
                    $this->line("  - buy-it-together");
                    $this->line("  - partners");
                    $this->line("  - banners");
                    $this->line("  - reviews");
                    $this->line("  - shipping-zones");
                    $this->line("  - shipping-zone-locations");
                    $this->line("  - shipping-zone-methods");
                    return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error("WordPress sync command failed ({$type})", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // CATEGORIES
    // ═══════════════════════════════════════════════════════════

    private function syncCategories($lang, $jobClass)
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products/categories', [
            'per_page' => 1,
            'page' => 1,
            'lang' => $lang
        ]);

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error("WordPress API connection failed (categories-{$lang})");
            return 1;
        }

        $totalPages = (int) $response->header('X-WP-TotalPages');
        $perPage = 10;

        $this->info("Total pages to process: {$totalPages}");
        Log::info("Dispatching {$totalPages} batch jobs (categories-{$lang})");

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages);
            $jobClass::dispatch($page, $perPage, $isLastBatch);
            $this->info("Dispatched batch for page {$page}");
        }

        $this->info('All batches dispatched successfully!');
        Log::info("All category batch jobs dispatched (categories-{$lang})", ['total_batches' => $totalPages]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // CITIES
    // ═══════════════════════════════════════════════════════════

    private function syncCities()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get('https://maximfood.com/wp-json/location/v1/all', [
            'per_page' => 1,
            'page' => 1,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error('WordPress API connection failed (cities)');
            return 1;
        }

        $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 1);
        $perPage = 10;

        $this->info("Total pages to process: {$totalPages}");
        Log::info("Dispatching {$totalPages} batch jobs (cities)");

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages);
            ProcessWordPressCitiesBatch::dispatch($page, $perPage, $isLastBatch);
            $this->info("Dispatched batch for page {$page}");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All city batch jobs dispatched', ['total_batches' => $totalPages]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // BUY IT TOGETHER
    // ═══════════════════════════════════════════════════════════

    private function syncBuyItTogether()
    {
        $woodmartFbtIds = $this->getAllWordPressProductFbtIds();

        if (empty($woodmartFbtIds)) {
            $this->warn('No products with FBT data found');
            Log::warning('No WordPress products with FBT data found');
            return 1;
        }

        $this->info("Found " . count($woodmartFbtIds) . " products with FBT data");
        Log::info("Found products with FBT data", ['count' => count($woodmartFbtIds)]);

        $perPage = 10;
        $batches = array_chunk($woodmartFbtIds, $perPage);
        $totalBatches = count($batches);

        $this->info("Dispatching {$totalBatches} batch jobs...");

        foreach ($batches as $index => $batch) {
            $isLastBatch = ($index === $totalBatches - 1);
            ProcessWordPressProductBuyItTogetherBatch::dispatch($batch, $index + 1, $perPage, $isLastBatch);
            $this->info("Dispatched batch " . ($index + 1) . " with " . count($batch) . " products");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All Buy It Together batch jobs dispatched', ['total_batches' => $totalBatches]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // PARTNERS
    // ═══════════════════════════════════════════════════════════

    private function syncPartners()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/media-api/v1/list', [
            'per_page' => 1,
            'page' => 1,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error('WordPress API connection failed (partners)');
            return 1;
        }

        $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 1);
        $perPage = 10;

        $this->info("Total pages to process: {$totalPages}");
        Log::info("Dispatching {$totalPages} batch jobs (partners)");

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages);
            ProcessWordPressPartnersBatch::dispatch($page, $perPage, $isLastBatch);
            $this->info("Dispatched batch for page {$page}");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All partner batch jobs dispatched', ['total_batches' => $totalPages]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // REVIEWS/RATES
    // ═══════════════════════════════════════════════════════════

    private function syncReviews()
    {
        $productIds = $this->getAllWordPressProductIds();

        if (empty($productIds)) {
            $this->warn('No products found for review sync');
            Log::warning('No WordPress products found for review sync');
            return 1;
        }

        $this->info("Found " . count($productIds) . " products for review sync");
        Log::info("Found products for review sync", ['count' => count($productIds)]);

        $perPage = 10;
        $batches = array_chunk($productIds, $perPage);
        $totalBatches = count($batches);

        $this->info("Dispatching {$totalBatches} batch jobs...");

        foreach ($batches as $index => $batch) {
            $isLastBatch = ($index === $totalBatches - 1);
            ProcessWordPressRatesBatch::dispatch($batch, $index + 1, $perPage, $isLastBatch);
            $this->info("Dispatched batch " . ($index + 1) . " with " . count($batch) . " products");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All review batch jobs dispatched', ['total_batches' => $totalBatches]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // SHIPPING ZONES
    // ═══════════════════════════════════════════════════════════

    private function syncShippingZones()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/shipping/zones', [
            'per_page' => 1,
            'page' => 1,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error('WordPress API connection failed (shipping-zones)');
            return 1;
        }

        $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 1);
        $perPage = 10;

        $this->info("Total pages to process: {$totalPages}");
        Log::info("Dispatching {$totalPages} batch jobs (shipping-zones)");

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages);
            ProcessWordPressShippingZonesBatch::dispatch($page, $perPage, $isLastBatch);
            $this->info("Dispatched batch for page {$page}");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All shipping zone batch jobs dispatched', ['total_batches' => $totalPages]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // SHIPPING ZONE LOCATIONS
    // ═══════════════════════════════════════════════════════════

    private function syncShippingZoneLocations()
    {
        $shippingZoneIds = $this->getAllWordPressShippingZoneIds();

        if (empty($shippingZoneIds)) {
            $this->warn('No shipping zones found');
            Log::warning('No WordPress shipping zones found for location sync');
            return 1;
        }

        $this->info("Found " . count($shippingZoneIds) . " shipping zones");
        Log::info("Found shipping zones for location sync", ['count' => count($shippingZoneIds)]);

        $perPage = 10;
        $batches = array_chunk($shippingZoneIds, $perPage);
        $totalBatches = count($batches);

        $this->info("Dispatching {$totalBatches} batch jobs...");

        foreach ($batches as $index => $batch) {
            $isLastBatch = ($index === $totalBatches - 1);
            ProcessWordPressShippingZoneLocationsBatch::dispatch($batch, $index + 1, $perPage, $isLastBatch);
            $this->info("Dispatched batch " . ($index + 1) . " with " . count($batch) . " zones");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All shipping zone location batch jobs dispatched', ['total_batches' => $totalBatches]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // SHIPPING ZONE METHODS
    // ═══════════════════════════════════════════════════════════

    private function syncShippingZoneMethods()
    {
        $shippingZoneIds = $this->getAllWordPressShippingZoneIds();

        if (empty($shippingZoneIds)) {
            $this->warn('No shipping zones found');
            Log::warning('No WordPress shipping zones found for method sync');
            return 1;
        }

        $this->info("Found " . count($shippingZoneIds) . " shipping zones");
        Log::info("Found shipping zones for method sync", ['count' => count($shippingZoneIds)]);

        $perPage = 10;
        $batches = array_chunk($shippingZoneIds, $perPage);
        $totalBatches = count($batches);

        $this->info("Dispatching {$totalBatches} batch jobs...");

        foreach ($batches as $index => $batch) {
            $isLastBatch = ($index === $totalBatches - 1);
            ProcessWordPressShippingZoneMethodsBatch::dispatch($batch, $index + 1, $perPage, $isLastBatch);
            $this->info("Dispatched batch " . ($index + 1) . " with " . count($batch) . " zones");
        }

        $this->info('All batches dispatched successfully!');
        Log::info('All shipping zone method batch jobs dispatched', ['total_batches' => $totalBatches]);

        return 0;
    }

    // ═══════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════

    private function getAllWordPressProductFbtIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
                'per_page' => $perPage,
                'page' => $page,
            ]);

            if ($response->successful()) {
                $products = $response->json();

                foreach ($products as $product) {
                    if (isset($product['meta_data'])) {
                        foreach ($product['meta_data'] as $meta) {
                            if (isset($meta['key']) && $meta['key'] === '_woodmart_fbt_products') {
                                $allIds[] = $product['id'];
                                break;
                            }
                        }
                    }
                }

                $page++;
                usleep(300000);

            } else {
                Log::error("Failed to fetch WordPress products for FBT ID sync", ['page' => $page]);
                break;
            }

        } while (count($products) === $perPage);

        return $allIds;
    }

    private function getAllWordPressProductIds()
    {
        $allIds = [];
        $page = 1;
        $perPage = 100;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products', [
                'per_page' => $perPage,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $products = $response->json();

                foreach ($products as $product) {
                    $allIds[] = $product['id'];
                }

                $page++;
                usleep(300000);

            } else {
                Log::error("Failed to fetch WordPress products for ID sync", ['page' => $page]);
                break;
            }

        } while (count($products) === $perPage);

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
                usleep(300000);

            } else {
                Log::error("Failed to fetch WordPress shipping zones for ID sync", ['page' => $page]);
                break;
            }

        } while (count($shippingZones) === $perPage);

        return $allIds;
    }



    private function syncBanners()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL') . '/wp-json/media-api/v1/banners', [
            'per_page' => 1,
            'page' => 1,
        ]);

        if (!$response->successful()) {
            $this->error('Failed to connect to WordPress API');
            Log::error('WordPress API connection failed (banners)');
            return 1;
        }

        $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 1);
        $perPage = 10;

        $this->info("Total pages to process: {$totalPages}");
        Log::info("Dispatching {$totalPages} batch jobs (banners)");

        for ($page = 1; $page <= $totalPages; $page++) {
            $isLastBatch = ($page === $totalPages);
            ProcessWordpressBannerBatch::dispatch($page, $perPage, $isLastBatch);
            $this->info("Dispatched banner batch for page {$page}");
        }

        $this->info('All banner batches dispatched successfully!');
        Log::info('All banner batch jobs dispatched', ['total_batches' => $totalPages]);

        return 0;
    }
}