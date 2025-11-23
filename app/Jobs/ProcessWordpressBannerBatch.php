<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\FeatureDeal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordpressBannerBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch; // إضافة flag للدفعة الأخيرة

    public function __construct($page, $perPage = 10, $isLastBatch = false)
    {
        Log::info('before syncbanners', [
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
        Log::info('ProcessWordPressBannerBatch job created');
        try {
            Log::info('hello');
            // مزامنة المنتجات العادية
            $this->syncbanners();
            Log::info('after BannerBatch', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);


        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function syncBanners()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get(env('WOOCOMMERCE_PORTAL_SITE_URL') . '/wp-json/media-api/v1/banners', [
            'per_page' => $this->perPage,
            'page' => $this->page,
            // 'orderby' => 'date',
            // 'order' => 'desc'
        ]);

        Log::info($response->getBody()->getContents());

        if ($response->successful()) {
            $banners = $response->json();

            Log::info("Processing batch", [
                'page' => $this->page,
                'banners_count' => count($banners),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            // truncate FeatureDeal before creation
            FeatureDeal::truncate();

            foreach ($banners['data'] as $banner) {
                DB::beginTransaction();

                try {
                    // $bannerRecord = Category::updateOrCreate(
                    FeatureDeal::create(
                        [
                            'type' => $banner['type'],
                            'url' => $banner['url'],
                            'category_id_en' => $banner['type'] == 'category' ? $banner['category_id_en'] : null,
                            'category_id_ar' => $banner['type'] == 'category' ? $banner['category_id_ar'] : null,
                        ]
                    );

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Category sync failed in batch", [
                        'page' => $this->page,
                        'category_id' => $banner['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_banners' => count($banners) // تعديل هنا
            ]);
        }
    }
}
