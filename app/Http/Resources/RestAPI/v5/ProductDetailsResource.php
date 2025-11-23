<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Models\DigitalProductVariation;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Wishlist;
use App\Models\BuyItTogether;

class ProductDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $this->additional['lang'] ?? 'en';

        $bundles = is_string($this->woodmart_fbt_bundles_id) ?
            json_decode($this->woodmart_fbt_bundles_id, true) :
            ($this->woodmart_fbt_bundles_id ?? []);
//        dd($bundles);
        $buy_together = BuyItTogether::whereIn('wordpress_id', $bundles)
            ->whereNotNull('wordpress_id')
            ->where('status', 'publish')
            ->get();
//        $lang = $this->additional['lang'] ?? null;

        $regular_product_discount = $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? (((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price'] * 100) : 0;

//        dd($buy_together);
        // Process buy-it-together products
        $buy_it_together = $this->processBuyTogether($buy_together,$regular_product_discount,$lang);
//        dd($buy_it_together);
        // Process product variations and attributes
        $variations = DigitalProductVariation::where('product_id', $this->wordpress_id)->get();
        $processedAttributes = $this->processVariationAttributes($variations);
//dd($this->resource['on_sale'] ? ((float) $this->resource['sale_price']?:(float) $this->resource['regular_price']): (float) $this->resource['regular_price']);
//        dd($lang);
//        dd($lang);

        return [
            'id' => $this->wordpress_id,
            'name' => $this->name,
            'category_ids' => json_decode($this->category_ids),
            'price_before_discount' => (float)( $this->resource['regular_price'] ?? 0),
            'price_after_discount' => (float) $this->resource['on_sale'] ? ((float) $this->resource['sale_price']?:(float) $this->resource['regular_price']): (float) $this->resource['regular_price'],
            'thumbnail' => $this->thumbnail,
            'images' => collect(json_decode($this->images, true))
                ->map(fn($image) => $image['src'])
                ->toArray(),
            'description' => $this->details,
            'short_description' => $this->description,
//            'in_wishlist' => auth()->check() ? Wishlist::where('customer_id', auth()->id())->where('product_id', $this->wordpress_id)->exists() : false,
            'in_wishlist' => auth()->check() ? Wishlist::where('customer_id', auth()->user()->id)->where('product_id', $this->wordpress_id ?? 0)->exists() || Wishlist::where('customer_id', auth()->user()->id)->where('product_id', $this->product_id ?? 0)->exists() : false,

            'slug' => $this->slug,
            'variant_product' => json_decode(
                str_replace("'", '"', $this->variation),
                true
            ),
            'product_stock_count' => (int) $this->current_stock,
            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? (((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price'] * 100) : 0,
            'rate' => (float) round(Review::where('product_id', $this->wordpress_id)->avg('rating'), 2),
            'is_taxable' => (bool) $this->is_taxable,
            'attributes' => $processedAttributes,
//            'related_ids' => productArrivalResource::collection(
//                Product::whereIn('wordpress_id',
//                    is_string($this->related_ids) ?
//                        json_decode($this->related_ids, true) ?? [] :
//                        ($this->related_ids ?? [])
//                )->whereNotNull('wordpress_id')
//                    ->get()
//            ),
            'related_ids' => collect(
                Product::whereIn('wordpress_id',
                    is_string($this->related_ids) ?
                        json_decode($this->related_ids, true) ?? [] :
                        ($this->related_ids ?? [])
                )->whereNotNull('wordpress_id')
                    ->get()
            )->map(function ($product) {
                $lang = $this->additional['lang'] ?? 'en';

                // Check if product needs translation
                if ($product->lang != $lang) {
                    $translationField = "translation_{$lang}";
                    $translationId = $product->$translationField ?? null;

                    if ($translationId) {
                        $translatedProduct = Product::where('wordpress_id', $translationId)
                            ->where('status', 1)
                            ->whereNotNull('slug')
                            ->first();

                        if ($translatedProduct) {
                            return productArrivalResource::make($translatedProduct)->additional(['lang' => $lang]);
                        }
                    }
                }

                return productArrivalResource::make($product)->additional(['lang' => $lang]);
            }),
            'translation'=>[
                'ar'=>$this->translation_ar,
                'en'=>$this->translation_en
            ],
            'buy_it_together' => $buy_it_together,
        ];
    }

    /**
     * Process buy-together products and variations
     */
//    private function processBuyTogether($buy_together,$regular_product_discount)
//    {
//        if ($buy_together->isEmpty()) {
//            return [];
//        }
//        $buy_together_ids = $buy_together->pluck('woodmart_fbt_product_id')->filter()->toArray();
////        dd($buy_together_ids);
//        if (empty($buy_together_ids)) {
//            return [];
//        }
//
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $buy_together_ids)->get();
//        $buy_together_products = Product::whereIn('wordpress_id', $buy_together_ids)->get();
//
//        $discounts_map = $buy_together->pluck('woodmart_fbt_product_discount', 'woodmart_fbt_product_id')->toArray();
//        $main_discounts_map = $buy_together->pluck('woodmart_main_products_discount', 'woodmart_fbt_product_id')->toArray();
//        $buy_together_product_ids = $buy_together->pluck('wordpress_id', 'woodmart_fbt_product_id')->toArray();
//        $buy_together_variation_ids = $buy_together->pluck('wordpress_id', 'woodmart_fbt_product_id')->toArray();
////        dd($discounts_map,$main_discounts_map,$buy_together_ids,$buy_together_variation_ids);
////        dd(['wordpress_id' => $buy_together->pluck('wordpress_id'), 'woucodmart_fbt_product_id' => $buy_together->pluck('woodmart_fbt_product_id')]);
//        $buy_it_together = collect();
////        dd($buy_together_product_ids);
//        // Process products
//        foreach ($buy_together_products as $product) {
//            $product_discount = $discounts_map[$product->wordpress_id] ?? null;
//            $main_product_discount = $main_discounts_map[$product->wordpress_id] ?? null;
//            $buy_together_ids = $buy_together_product_ids[$product->wordpress_id] ?? null;
////            dd($buy_together_ids);
////            dd($main_product_id);
//            $buy_it_together->push(
//                productArrivalResource::make($product)->additional([
//                    'buy_together' => true,
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'buy_together_id'=>   $buy_together_ids,
////                    'product_main_discount' => (float)$main_product_discount + (($product = Product::where('wordpress_id', $this->wordpress_id)->first()) && $product->resource['on_sale'] && !empty($product->resource['sale_price']) ? round((((float)$product->resource['regular_price'] - (float)$product->resource['sale_price']) / (float)$product->resource['regular_price']) * 100) : 0),
//                    'product_main_discount' => (int)$main_product_discount +$regular_product_discount
//                ])
//            );
//        }
//
//        //+
//        //                        (($product = Product::where('wordpress_id', $this->wordpress_id)->first()) &&
//        //                        $product->on_sale && !empty($product->sale_price) ?
//        //
//        //                            (round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100)) :
//        //                            0
//        //                        ),
//
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            $product_discount = $discounts_map[$variation->wordpress_id] ?? null;
//            $main_product_discount = $main_discounts_map[$variation->wordpress_id] ?? null;
//
////            $parentProduct = Product::where('wordpress_id', $variation->product_id)->first();
//            $buy_together_ids = $buy_together_variation_ids[$variation->wordpress_id] ?? null;
//
////            if ($parentProduct) {
////                $buy_it_together->push(
////                    productArrivalResource::make($parentProduct)->additional([
////                        'buy_together' => true,
////                        'variation_id' => $variation->wordpress_id,
////                        'product_discount' => $product_discount,
////                        'buy_together_id'=>$buy_together_ids,
////                        'product_main_discount' => $main_product_discount,
////                    ])
////                );
////            } else {
//                // Fallback for variations without parent products
//                $buy_it_together->push([
//                    'id' => $variation->wordpress_id,
//                    'name' => null,
//                    'price_before_discount' => (float) $variation->regular_price ?? 0,
//                    'price_after_discount' => (float) $variation->price ?? 0,
//                    'thumbnail' => $variation->image ?? null,
//                    'images' => $variation->image ? [$variation->image] : [],
//                    'in_wishlist' => false,
//                    'slug' => null,
//                    'product_stock_count' => 0,
//                    'discount' => 0,
//                    'is_taxable' => false,
//                    'buy_together_id'=>$buy_together_ids,
////                    'product_main_discount' => (float) $main_product_discount + Product::where('wordpress_id',$this->wordpress_id)->first()->resource['on_sale'] && !empty(Product::where('wordpress_id',$this->wordpress_id)->first()->resource['sale_price']) ? round((((float)Product::where('wordpress_id',$this->wordpress_id)->first()->resource['regular_price'] - (float)Product::where('wordpress_id',$this->wordpress_id)->first()->resource['sale_price']) / (float)Product::where('wordpress_id',$this->wordpress_id)->first()->resource['regular_price']) * 100) : 0,
////                dd(round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100)),
////                    'product_main_discount' => (float)$main_product_discount +
////                        (($product = Product::where('wordpress_id', $this->wordpress_id)->first()) &&
////                        $product->on_sale &&
////                        !empty($product->sale_price) ?
////                            round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100),
//                    'main_product_discount' => (int)$main_product_discount +$regular_product_discount ,
//                    'buy_together_discount' => $product_discount,
//                    'variant' => DigitalProductVariationResource::make($variation),
////                    'variants' => []
//                ]);
////            }
//        }
//
//        return $buy_it_together->values()->toArray();
//    }





//    private function processBuyTogether($buy_together, $regular_product_discount)
//    {
//        if ($buy_together->isEmpty()) {
//            return [];
//        }
//
//        // Decode JSON strings and flatten to get all unique product IDs
//        $buy_together_ids = $buy_together->pluck('woodmart_fbt_product_id')
//            ->filter()
//            ->flatMap(function ($ids) {
//                // Decode JSON string to array
//                $decoded = is_string($ids) ? json_decode($ids, true) : $ids;
//                return is_array($decoded) ? $decoded : [$decoded];
//            })
//            ->filter()
//            ->unique()
//            ->toArray();
//
//        if (empty($buy_together_ids)) {
//            return [];
//        }
//
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $buy_together_ids)->get();
//        $buy_together_products = Product::whereIn('wordpress_id', $buy_together_ids)->get();
//
//        // Create lookup map: each product ID => its buy_together record
//        $buy_together_lookup = [];
//        foreach ($buy_together as $record) {
//            $product_ids = json_decode($record->woodmart_fbt_product_id, true);
//            if (is_array($product_ids)) {
//                foreach ($product_ids as $product_id) {
//                    $buy_together_lookup[$product_id] = $record;
//                }
//            }
//        }
//
//        $buy_it_together = collect();
//
//        // Process products
//        foreach ($buy_together_products as $product) {
//            $buy_together_record = $buy_together_lookup[$product->wordpress_id] ?? null;
//
//            if ($buy_together_record) {
//                $product_discount = $buy_together_record->woodmart_fbt_product_discount ?? null;
//                $main_product_discount = $buy_together_record->woodmart_main_products_discount ?? null;
//                $buy_together_id = $buy_together_record->wordpress_id ?? null;
//
//                $buy_it_together->push(
//                    productArrivalResource::make($product)->additional([
//                        'buy_together' => true,
//                        'variation_id' => null,
//                        'product_discount' => $product_discount,
//                        'buy_together_id' => $buy_together_id,
//                        'product_main_discount' => (int)$main_product_discount + $regular_product_discount
//                    ])
//                );
//            }
//        }
//
//        $buy_together_variation_id=null;
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            $buy_together_record = $buy_together_lookup[$variation->wordpress_id] ?? null;
//
//            if ($buy_together_record) {
//                $product_discount = $buy_together_record->woodmart_fbt_product_discount ?? null;
//                $main_product_discount = $buy_together_record->woodmart_main_products_discount ?? null;
//                $buy_together_id = $buy_together_record->wordpress_id ?? null;
//
//                $buy_it_together->push([
////                    'id' => $variation->wordpress_id,
////                    'name' => null,
////                    'price_before_discount' => (float) ($variation->regular_price ?? 0),
////                    'price_after_discount' => (float) ($variation->price ?? 0),
////                    'thumbnail' => $variation->image ?? null,
////                    'images' => $variation->image ? [$variation->image] : [],
////                    'in_wishlist' => false,
////                    'slug' => null,
////                    'product_stock_count' => 0,
////                    'discount' => 0,
////                    'is_taxable' => false,
////                    'buy_together_id' => $buy_together_id,
////                    'main_product_discount' => (int)$main_product_discount + $regular_product_discount,
////                    'buy_together_discount' => $product_discount,
//                    'variant' => DigitalProductVariationResource::make($variation,$buy_together_variation_id),
//                ]);
//            }
//        }
//
//        return $buy_it_together->values()->toArray();
//    }


//        private function processBuyTogether($buy_together, $regular_product_discount)
//    {
////        dd($buy_together);
//        if ($buy_together->isEmpty()) {
//            return [];
//        }
//
//        // Decode JSON strings and flatten to get all unique product IDs
//        $buy_together_ids = $buy_together->pluck('woodmart_fbt_product_id')
//            ->filter()
//            ->flatMap(function ($ids) {
//                // Decode JSON string to array
//                $decoded = is_string($ids) ? json_decode($ids, true) : $ids;
//                return is_array($decoded) ? $decoded : [$decoded];
//            })
//            ->filter()
//            ->unique()
//            ->toArray();
////        dd($buy_together_ids);
//
//        if (empty($buy_together_ids)) {
//            return [];
//        }
//
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $buy_together_ids)->get();
//        $buy_together_products = Product::whereIn('wordpress_id', $buy_together_ids)->get();
//
//        // Create lookup map: each product ID => its buy_together record
//        $buy_together_lookup = [];
//        foreach ($buy_together as $record) {
//            $product_ids = json_decode($record->woodmart_fbt_product_id, true);
//            if (is_array($product_ids)) {
//                foreach ($product_ids as $product_id) {
//                    $buy_together_lookup[$product_id] = $record;
//                }
//            }
//        }
//
//        $buy_it_together = collect();
//
//        // Process products
//        foreach ($buy_together_products as $product) {
//            $buy_together_record = $buy_together_lookup[$product->wordpress_id] ?? null;
//
//            if ($buy_together_record) {
//                $product_discount = $buy_together_record->woodmart_fbt_product_discount ?? null;
//                $main_product_discount = $buy_together_record->woodmart_main_products_discount ?? null;
//                $buy_together_id = $buy_together_record->wordpress_id ?? null;
//
//                $buy_it_together->push(
//                    productArrivalResource::make($product)->additional([
//                        'buy_together' => true,
//                        'variation_id' => null,
//                        'product_discount' => $product_discount,
//                        'buy_together_id' => $buy_together_id,
//                        'product_main_discount' => (int)$main_product_discount + $regular_product_discount
//                    ])
//                );
//            }
//        }
//
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            $buy_together_record = $buy_together_lookup[$variation->wordpress_id] ?? null;
//
//            if ($buy_together_record) {
//                $product_discount = $buy_together_record->woodmart_fbt_product_discount ?? null;
//                $main_product_discount = $buy_together_record->woodmart_main_products_discount ?? null;
//                $buy_together_id = $buy_together_record->wordpress_id ?? null;
//
//                // Get the parent product for the variation
//                $parent_product = Product::where('wordpress_id', $variation->product_id)->first();
//
//                if ($parent_product) {
//                    $buy_it_together->push(
//                        productArrivalResource::make($parent_product)->additional([
//                            'buy_together' => true,
//                            'variation_id' => $variation->wordpress_id,
//                            'product_discount' => $product_discount,
//                            'buy_together_id' => $buy_together_id,
//                            'product_main_discount' => (int)$main_product_discount + $regular_product_discount
//                        ])
//                    );
//                }
//            }
//        }
//
//        return $buy_it_together->values()->toArray();
//    }







    private function processBuyTogether($buy_together, $regular_product_discount,$lang='en')
    {
        if ($buy_together->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($buy_together as $buy_together_record) {
            // Decode the product IDs for this specific buy_together record
            $product_ids = json_decode($buy_together_record->woodmart_fbt_product_id, true);

            if (!is_array($product_ids) || empty($product_ids)) {
                continue;
            }

            $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $product_ids)
                ->where('stock_status', 'instock')
                ->whereHas('product', function($query) {
                    $query->where('status', 1)->where('current_stock', '>=', 1);
                })
                ->get();

            $buy_together_products = Product::whereIn('wordpress_id', $product_ids)
                ->where('status', 1)
                ->where('current_stock', '>=', 1) // Changed from 1 to >=1 if you want stock > 0
                ->get();

            $products_list = collect();

            // Process products
            foreach ($buy_together_products as $product) {
                if ($product->wordpress_id!=$this->wordpress_id){
//                    $product_discount = (float)($buy_together_record->woodmart_fbt_product_discount ?? null);
                    $main_product_discount = (float)($buy_together_record->woodmart_main_products_discount ?? null);
                    $buy_together_id = $buy_together_record->wordpress_id ?? null;
//dd($main_product_discount + $regular_product_discount);
                    $products_list->push(
                        productArrivalResource::make($product)->additional([
                            'lang'=>$lang,
                            'buy_together' => true,
                            'variation_id' => null,
//                            'product_discount' => (float) $product_discount,
                            'buy_together_id' => $buy_together_id,
//                            'product_main_discount' => (float) ($main_product_discount + $regular_product_discount),
                            'product_main_discount' =>(float)$main_product_discount?:(float)($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? (((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price'] * 100) : 0) + $regular_product_discount
                        ])
                    );
                }

            }

            // Process variations
            foreach ($buy_together_variations as $variation) {
                if ($variation->product_id!=$this->wordpress_id){
//                    $product_discount = (float)($buy_together_record->woodmart_fbt_product_discount ?? null);
                    $main_product_discount = (float)($buy_together_record->woodmart_main_products_discount ?? null);
                    $buy_together_id = $buy_together_record->wordpress_id ?? null;

                    // Get the parent product for the variation
                    $parent_product = Product::where('wordpress_id', $variation->product_id)->first();

                    if ($parent_product) {
                        $products_list->push(
                            productArrivalResource::make($parent_product)->additional([
                                'lang'=>$lang,
                                'buy_together' => true,
                                'variation_id' => $variation->wordpress_id,
//                                'product_discount' => $product_discount,
                                'buy_together_id' => $buy_together_id,
                                'product_main_discount' => (float)($main_product_discount?:($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? (((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price'] * 100) : 0) + (float)$regular_product_discount)
                            ])
                        );
                    }
                }

            }

            $mainProductDiscount=(float)($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? (((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100 : 0);
//            dd($buy_together_record->woodmart_main_products_discount,$mainProductDiscount);

//            $product_discount = ($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0)?: null;
//            $main_product_discount=(float)($buy_together_record->woodmart_main_products_discount ?:($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0)?: null);
            // Add this group to results
            $result[] = [
                'buy_together_id' => $buy_together_record->wordpress_id,
                'main_product_discount' => (float)((float)$buy_together_record->woodmart_main_products_discount ) ,
                'product_discount' => (float)($buy_together_record->woodmart_fbt_product_discount ?? null),
                '_woodmart_fbt_products' => $products_list->values()->toArray()
            ];
        }

        return $result;
    }






    /**
     * Process variation attributes
     */
    private function processVariationAttributes($variations)
    {
        return $variations->flatMap(function ($variation) {
            return collect(json_decode($variation->attributes ?? '[]', true))
                ->map(function ($attribute) use ($variation) {
                    return [
                        'name' => $attribute['name'] ?? '',
                        'option' => $attribute['option'] ?? '',
                        'variation' => $variation
                    ];
                });
        })->groupBy('name')
            ->map(function ($group, $name) {
                $options = $group->groupBy('option')->map(function ($optionGroup) {
                    return [
                        'value' => $optionGroup->first()['option'] ?? '',
                        'variation' => DigitalProductVariationResource::make(
                            $optionGroup->first()['variation']
                        )
                    ];
                })->values();

                return [
                    'name' => $name,
                    'options' => $options
                ];
            })->values()->toArray();
    }

    /**
     * Safely decode images from JSON or return array.
     */
    private function prepareImages($images): array
    {
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (!is_array($images)) {
            return [];
        }

        return collect($images)
            ->pluck('image_name')
            ->filter()
            ->map(function ($imageName) {
                return asset('storage/product/' . $imageName);
            })
            ->values()
            ->toArray();
    }
}
