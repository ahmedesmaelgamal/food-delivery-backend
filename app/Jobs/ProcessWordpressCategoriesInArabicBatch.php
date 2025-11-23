<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordpressCategoriesInArabicBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch;

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;

        Log::info('ProcessWordpressCategoriesInArabicBatch job created', [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'isLastBatch' => $this->isLastBatch
        ]);
    }

    public function handle()
    {
        Log::info('ProcessWordPressCategoriesInArabicBatch job started');

        try {
            $this->syncCategories();
            Log::info('After syncCategories in arabic', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);

            if ($this->isLastBatch) {
                $this->syncDeletedCategories();
            }

        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncCategories()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)
            ->withoutVerifying()
            ->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products/categories', [
                'per_page' => $this->perPage,
                'page' => $this->page,
                'lang' => 'ar'
            ]);

        if ($response->successful()) {
            $categories = $response->json();

            Log::info("Processing categories batch", [
                'page' => $this->page,
                'categories_count' => count($categories),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($categories as $category) {
                try {
                    $categoryImage = $this->getCategoryImage($category['id']);
                    if ($category['lang']=='ar'){

                        Category::updateOrCreate(
                            ['wordpress_id' => $category['id']],
                            [
                                'name' => $category['name'],
                                'slug' => $category['slug'],
                                'parent_id' => $category['parent'] ?? null,
                                'description' => $category['description'] ?? '',
                                'icon' => $categoryImage,
                                'lang' => 'ar',
                                'home_status' => $category['translations']['ar']?1:0
                    //                            'home_status' => 1
                            ]
                        );

                        Log::info("Category synced successfully", [
                            'wordpress_id' => $category['id'],
                            'name' => $category['name'],
                            'image' => $categoryImage
                        ]);

                    }
                } catch (\Exception $e) {
                    Log::error("Category sync failed", [
                        'page' => $this->page,
                        'category_id' => $category['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_categories' => count($categories)
            ]);
        } else {
            Log::error("Failed to fetch categories from WordPress", [
                'page' => $this->page,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        }
    }

    private function getCategoryImage($categoryId)
    {
        $imageMap = [
            539 => '/storage/app/public/category-images/sushi.jpg',
            520 => '/storage/app/public/category-images/shrimp.jpg',
            583 => '/storage/app/public/category-images/Live-&-Fresh.jpg',
            613 => '/storage/app/public/category-images/caught-fish.jpg',
            584 => '/storage/app/public/category-images/Asian-cuisine.jpg',
            591 => '/storage/app/public/category-images/sashimi.jpg',
            587 => '/storage/app/public/category-images/salmon.jpg',
            594 => '/storage/app/public/category-images/smoked.jpg',
            592 => '/storage/app/public/category-images/seafood-cocktail.jpg', // Fixed space in filename
            585 => '/storage/app/public/category-images/Caviar.jpg',
            586 => '/storage/app/public/category-images/meals-and-salads.jpg',
            624 => '/storage/app/public/category-images/gold.jpeg',
        ];

        return $imageMap[$categoryId] ?? null;
    }

    private function syncDeletedCategories()
    {
        try {
            Log::info("Starting deleted categories sync in last batch");

            $wordpressCategoryIds = $this->getAllWordPressCategoryIds();

            if (empty($wordpressCategoryIds)) {
                Log::warning("No WordPress category IDs found for deletion sync");
                return;
            }

            $localCategories = Category::select('id', 'wordpress_id', 'name')->get();
            $deletedCount = 0;

            foreach ($localCategories as $localCategory) {
                if (!in_array($localCategory->wordpress_id, $wordpressCategoryIds)) {
                    try {
                        $localCategory->delete();
                        $deletedCount++;

                        Log::info("Deleted category", [
                            'wordpress_id' => $localCategory->wordpress_id,
                            'name' => $localCategory->name
                        ]);

                    } catch (\Exception $e) {
                        Log::error("Failed to delete category", [
                            'wordpress_id' => $localCategory->wordpress_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Deleted categories sync completed", [
                'deleted_count' => $deletedCount,
                'total_wordpress_categories' => count($wordpressCategoryIds),
                'total_local_categories' => $localCategories->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Deleted categories sync failed", ['error' => $e->getMessage()]);
        }
    }

    private function getAllWordPressCategoryIds()
    {
        $allIds = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(
                env('CONSUMER_KEY'),
                env('CONSUMER_SECRET')
            )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL').'/wp-json/wc/v3/products/categories', [
                'per_page' => 100,
                'page' => $page,
                '_fields' => 'id'
            ]);

            if ($response->successful()) {
                $categories = $response->json();

                foreach ($categories as $category) {
                    $allIds[] = $category['id'];
                }

                $page++;

                // Delay between requests
                usleep(300000);
            } else {
                Log::error("Failed to fetch WordPress categories for deletion sync", ['page' => $page]);
                break;
            }

        } while (count($categories) == 100);

        return $allIds;
    }
}