<?php

namespace App\Jobs;

use App\Models\City;
use App\Models\Country;
use App\Models\State; // Add this import
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWordPressCitiesBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $page;
    public $perPage;
    public $isLastBatch;

    public function __construct($page, $perPage = 10, $isLastBatch = true)
    {
        $this->page = $page;
        $this->perPage = $perPage;
        $this->isLastBatch = $isLastBatch;

        Log::info('ProcessWordPressCitiesBatch created', [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'isLastBatch' => $this->isLastBatch
        ]);
    }

    public function handle()
    {
        Log::info('ProcessWordPressCitiesBatch job started');
        try {
            $this->syncCities();

            Log::info('syncCities completed', [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'isLastBatch' => $this->isLastBatch
            ]);

            // إذا كانت هذه الدفعة الأخيرة، قم بمزامنة الحذف
            if ($this->isLastBatch) {
//                $this->syncDeletedCities();
            }

        } catch (\Exception $e) {
            Log::error("Batch job failed", [
                'page' => $this->page,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function syncCities()
    {
        $response = Http::withBasicAuth(
            env('CONSUMER_KEY'),
            env('CONSUMER_SECRET')
        )->timeout(60)->get('https://maximfood.com/wp-json/location/v1/all', [
            'per_page' => $this->perPage,
            'page' => $this->page,
        ]);

        if ($response->successful()) {
            $data = $response->json()['data'];

            // التحقق من نوع البيانات قبل المعالجة
            if (!is_array($data)) {
                Log::warning("Invalid data format received", [
                    'page' => $this->page,
                    'data_type' => gettype($data),
                    'data' => $data
                ]);
                return;
            }

            // التحقق من أن البيانات ليست فارغة
            if (empty($data)) {
                Log::info("No data found for page", [
                    'page' => $this->page
                ]);
                return;
            }

            Log::info("Processing batch", [
                'page' => $this->page,
                'countries_count' => count($data),
                'per_page' => $this->perPage,
                'is_last_batch' => $this->isLastBatch
            ]);

            foreach ($data as $countryData) {
                Log::info("daaaaaaaaaataaaaaaaaaaaaaaaaaaaaaa", [
                    '$data'=>$data,
                    '$countryData'=>$countryData
                ]);

                // التحقق من أن countryData هو مصفوفة
                if (!is_array($countryData)) {
                    Log::warning("Invalid country data format", [
                        'page' => $this->page,
                        'country_data' => $countryData
                    ]);
                    continue;
                }

                // التحقق من وجود البيانات المطلوبة
                if (!isset($countryData['code']) || !isset($countryData['name'])) {
                    Log::warning("Missing required country data", [
                        'page' => $this->page,
                        'country_data' => $countryData
                    ]);
                    continue;
                }

                DB::beginTransaction();

                try {
                    // إنشاء أو تحديث الدولة
                    $country = Country::updateOrCreate(
                        ['code' => $countryData['code']],
                        [
                            'name' => $countryData['name'],
                            'code' => $countryData['code'],
                        ]
                    );

                    Log::info("Country processed", [
                        'country_id' => $country->id,
                        'country_code' => $country->code
                    ]);

                    // معالجة الولايات
                    if (isset($countryData['states']) && is_array($countryData['states'])) {
                        foreach ($countryData['states'] as $stateData) {
                            if (!is_array($stateData) || !isset($stateData['code']) || !isset($stateData['name'])) {
                                Log::warning("Invalid state data", [
                                    'country_code' => $countryData['code'],
                                    'state_data' => $stateData
                                ]);
                                continue;
                            }

                            // إنشاء أو تحديث الولاية
                            $state = State::updateOrCreate(
                                [
                                    'code' => $stateData['code'],
                                    'country_id' => $country->id
                                ],
                                [
                                    'name' => $stateData['name'],
                                    'code' => $stateData['code'],
                                    'country_id' => $country->id,
                                ]
                            );

                            Log::info("State processed", [
                                'state_id' => $state->id,
                                'state_code' => $state->code,
                                'country_code' => $countryData['code']
                            ]);

                            // معالجة المدن
                            if (isset($stateData['cities']) && is_array($stateData['cities'])) {
                                foreach ($stateData['cities'] as $cityData) {
                                    if (!is_array($cityData) || !isset($cityData['code']) || !isset($cityData['name'])) {
                                        Log::warning("Invalid city data", [
                                            'country_code' => $countryData['code'],
                                            'state_code' => $stateData['code'],
                                            'city_data' => $cityData
                                        ]);
                                        continue;
                                    }

                                    // إنشاء أو تحديث المدينة
                                    City::updateOrCreate(
                                        [
                                            'code' => $cityData['code'],
                                            'state_id' => $state->id
                                        ],
                                        [
                                            'name' => $cityData['name'],
                                            'code' => $cityData['code'],
                                            'state_id' => $state->id,
                                        ]
                                    );

                                    Log::info("City processed", [
                                        'city_code' => $cityData['code'],
                                        'state_code' => $stateData['code'],
                                        'country_code' => $countryData['code']
                                    ]);
                                }
                            }
                        }
                    }

                    DB::commit();

                    Log::info("Successfully processed country", [
                        'country_code' => $countryData['code'],
                        'country_name' => $countryData['name'],
                        'states_count' => isset($countryData['states']) ? count($countryData['states']) : 0
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Country sync failed in batch", [
                        'page' => $this->page,
                        'country_code' => $countryData['code'] ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info("Batch completed successfully", [
                'page' => $this->page,
                'processed_countries' => count($data)
            ]);
        } else {
            Log::error("API request failed", [
                'status' => $response->status(),
                'response' => $response->body(),
                'page' => $this->page
            ]);
        }
    }

}
