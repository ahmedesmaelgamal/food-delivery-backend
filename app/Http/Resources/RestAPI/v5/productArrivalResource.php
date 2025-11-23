<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Models\DigitalProductVariation;
use App\Models\CartProduct;
use App\Models\BuyItTogether;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class productArrivalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
//    public function toArray(Request $request): array
//    {
//        $variation_id = $this->additional['variation_id'] ?? null;
//        $product_discount = (float)($this->additional['product_discount'] ?? null);
////        $main_product_discount_with_buy_together = (float)($this->additional['main_product_with_buy_together_discount'] ?? null);
//        $main_product_discount= (float)($this->additional['main_product_discount'] ?? null);
//        $buy_together_id = $this->additional['buy_together_id'] ?? null;
//        $is_cart_buy_together = $this->additional['is_cart_buy_together'] ?? null;
//        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
//        $is_cart = $this->additional['is_cart'] ?? null;
//
//        // Check if this is already a nested buy-together product to prevent infinite recursion
//        $is_nested = $this->additional['is_nested'] ?? false;
//
//        // If variation_id is passed, get the variation data
//        $variation = null;
//        if ($variation_id) {
//            $variation = DigitalProductVariation::where('wordpress_id', $variation_id)->first();
//        }
//
//        // Use variation data if available, otherwise use product data
//        $regular_price = $variation ? (float)($variation->regular_price ?? 0) : (float)($this->resource['regular_price'] ?? 0);
//        $sale_price = $variation ? (float)($variation->sale_price ?? 0) : (float)($this->resource['sale_price'] ?? 0);
//        $on_sale = $variation ? (bool)($variation->on_sale ?? false) : (bool)($this->resource['on_sale'] ?? false);
//        $current_stock = $variation ? (int)($variation->stock_status === 'instock' ? 999 : 0) : (int)($this->resource['current_stock'] ?? 0);
//
//        // Get variation image or fallback to product thumbnail
//        $thumbnail = $variation && $variation->image
//            ? $variation->image
//            : ($this->thumbnail ?? null);
//
//        $regular_product_discount = $on_sale && !empty($sale_price)
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//        // CRITICAL: Only process buy-together for root products, NEVER for nested ones
//        $buy_it_together = null;
//        $buy_together = null;
//
//        if (!$is_nested && $buy_together_id) {
//            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
//                ->whereNotNull('wordpress_id')
//                ->where('status', 'publish')
//                ->first();
//
//            if ($buy_together) {
//                $buy_it_together = $this->processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount);
//            }
//        }
//
//        // Calculate the discount to apply to the main product
//        $buy_together_discount = 0;
//        if ($buy_it_together && isset($buy_it_together['main_product_discount'])) {
//            $buy_together_discount = (float)$buy_it_together['main_product_discount'];
//        } elseif ($main_product_discount > 0) {
//            $buy_together_discount = (float)$main_product_discount;
//        }
//
//        // Calculate base price from variation or product
//        $base_price = $variation_id && $variation
//            ? ($sale_price  ?: ($regular_price ?: 0))
//            : ($this->resource['regular_price']  ?? 0);
//
//        $product_main_discount = $on_sale && !empty($sale_price)
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
////        dd($product_main_discount);
//
//
//        $total_discount = $buy_together_discount
//            ? $buy_together_discount + $product_main_discount
//            : $product_main_discount;
//
//        $price_after_discount = $base_price - ($base_price * ($total_discount / 100));
//        if (!$buy_together_id){
//            $price_after_discount=$this->sale_price?: $this->unit_price?: $this->regular_price;
//        }
//
//        $data = [
//            'id' => $this->wordpress_id ?? null,
//            'name' => $this->name ?? null,
//            'category_ids' => json_decode($this->category_ids ?? '[]') ?? null,
//            'price_before_discount' => $regular_price,
//            'price_after_discount' => (integer) $price_after_discount,
//            'thumbnail' => $thumbnail,
//            'images' => $variation && $variation->image
//                ? [$variation->image]
//                : collect(json_decode($this->images ?? '[]', true))
//                    ->map(fn($image) => $image['src'] ?? null)
//                    ->filter()
//                    ->toArray(),
//            'in_wishlist' => auth()->check()
//                ? Wishlist::where('customer_id', auth()->user()->id)
//                    ->where(function($query) use ($variation_id) {
////                        if ($variation_id) {
////                            $query->where('variant_id', $variation_id);
////                        } else {
//                            $query->where('product_id', $this->wordpress_id ?? 0)
//                                ->orWhere('product_id', $this->product_id ?? 0);
////                        }
//                    })
//                    ->exists()
//                : false,
//            'slug' => $this->slug ?? null,
//            'product_stock_count' => $current_stock,
//            'discount' => $product_main_discount,
//            'is_taxable' => (bool)($this->is_taxable ?? false),
//            'buy_together_id' => $is_nested ? null : $buy_together_id,
//            'buy_it_together' => $buy_it_together,
//            'main_product_discount' => (float) $main_product_discount,
//            'buy_together_discount' => (float) $product_discount,
//            'order_details_count' => $this->order_details_count ?? 0,
//        ];
//
//        if (auth()->check()) {
//            $userId = auth()->id();
//            if ($buy_together_id == null) {
//                if ($variation_id) {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where('product_id', @$this->wordpress_id)
//                        ->where('variant_id', $variation_id)
//                        ->whereNull('buy_together_id')
//                        ->first()->quantity ?: 0;
//                } else {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where('product_id', @$this->wordpress_id)
//                        ->whereNull('variant_id')
//                        ->whereNull('buy_together_id')
//                        ->first()->quantity ?: 0;
//                }
//            } elseif ($buy_together_id != null) {
//                if ($variation_id) {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where('product_id', @$this->wordpress_id)
//                        ->where('variant_id', $variation_id)
//                        ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%')
//                        ->first()->quantity ?: 0;
//                } else {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where('product_id', @$this->wordpress_id)
//                        ->where('buy_together_id', $buy_together_id)
//                        ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%')
//                        ->first()->quantity ?: 0;
//                }
//            }
//        } else {
//            $data['cart_count'] = 0;
//        }
//
//        $data['variant'] = $variation_id != null && $variation
//            ? DigitalProductVariationResource::make($variation)->additional([
////                'price_after_discount' => $price_after_discount,
//            ])
//            : null;
//
//        $data['variants'] = $variation_id == null
//            ? DigitalProductVariationResource::collection($this->digitalVariation ?? [])
//            : [];
//
//        return $data;
//    }
//
//    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount)
//    {
////        dd($selected_buy_together_ids);
//        $product_ids = json_decode($selected_buy_together_ids);
//
//        if (!is_array($product_ids) || empty($product_ids)) {
//            return null;
//        }
//
//        $products_list = collect();
//        $product_discount = (float)($buy_together->woodmart_fbt_product_discount ?? 0);
//        $main_product_discount = (float)($buy_together->woodmart_main_products_discount ?? 0);
//        // First, check which IDs are variations and which are products
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $product_ids)->get();
//        $variation_ids = $buy_together_variations->pluck('wordpress_id')->toArray();
//
//        // Get product IDs that are NOT variations
//            $product_only_ids = array_diff($product_ids, $variation_ids);
//        $buy_together_products = Product::whereIn('wordpress_id', $product_only_ids)->where('status',1)->where('current_stock','>=',1)->get();
//        // Process standalone products (not variations)
//        foreach ($buy_together_products as $product) {
//            // Skip if this is the current product
//            if ($product->wordpress_id == $this->wordpress_id) {
//                continue;
//            }
//
//            $products_list->push(
//                productArrivalResource::make($product)->additional([
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'product_main_discount' => (float) ($main_product_discount + $regular_product_discount),
//                    'buy_together_id' => null,
//                    'is_nested' => true
//                ])
//            );
//        }
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            // Skip if the variation belongs to the current product
//            if ($variation->product_id == $this->wordpress_id) {
//                continue;
//            }
//
//            // Get the parent product for this variation
//            $parent_product = Product::where('wordpress_id', $variation->product_id)->first();
////
//            if ($parent_product) {
//
//                $products_list->push(
//                    productArrivalResource::make($parent_product)->additional([
//                        'variation_id' => $variation->wordpress_id,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => (float)($main_product_discount ?: $regular_product_discount),
//                        'buy_together_id' => null,
//                        'is_nested' => true
//                    ])
//                );
//            }
//        }
//
//        if ($products_list->isEmpty()) {
//            return null;
//        }
//
//        return [
//            'buy_together_id' => $buy_together->wordpress_id,
//            'main_product_discount' => (float)($buy_together->woodmart_main_products_discount ?: $regular_product_discount ?: 0),
//            'product_discount' => $product_discount,
//            '_woodmart_fbt_products' => $products_list->values()->toArray()
//        ];
//    }




    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
//    public function toArray(Request $request): array
//    {
////        dd($this->additional['lang']);
//        $lang = $this->additional['lang'] ?? 'en';
//        $variation_id = $this->additional['variation_id'] ?? null;
//        $product_discount = (float)($this->additional['product_discount'] ?? null);
//        $main_product_discount = (float)($this->additional['main_product_discount'] ?? null);
//        $buy_together_id = $this->additional['buy_together_id'] ?? null;
//        $is_cart_buy_together = $this->additional['is_cart_buy_together'] ?? null;
//        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
//        $is_cart = $this->additional['is_cart'] ?? null;
//
//        // Check if this is already a nested buy-together product to prevent infinite recursion
//        $is_nested = $this->additional['is_nested'] ?? false;
//
//        // Handle language translation for the main product
//        $product = $this->resource;
//        if ($product && $product->lang != $lang) {
//            $translationField = "translation_{$lang}";
//            $translationId = $product->$translationField ?? null;
//
//            if ($translationId) {
//                $translatedProduct = Product::where('wordpress_id',$translationId)->first();
//                if ($translatedProduct) {
//                    $product = $translatedProduct;
//                    // Update the resource to use translated product
//                    $this->resource = $translatedProduct;
//                }
//            }
//        }
//
//        // If variation_id is passed, get the variation data
//        $variation = null;
//        if ($variation_id) {
//            $variation = DigitalProductVariation::where('wordpress_id', $variation_id)->first();
//        }
//
//        // Use variation data if available, otherwise use product data
//        $regular_price = $variation ? (float)($variation->regular_price ?? 0) : (float)($this->resource['regular_price'] ?? 0);
//        $sale_price = $variation ? (float)($variation->sale_price ?? 0) : (float)($this->resource['sale_price'] ?? 0);
//        $on_sale = $variation ? (bool)($variation->on_sale ?? false) : (bool)($this->resource['on_sale'] ?? false);
//        $current_stock = $variation ? (int)($variation->stock_status === 'instock' ? 999 : 0) : (int)($this->resource['current_stock'] ?? 0);
//
//        // Get variation image or fallback to product thumbnail
//        $thumbnail = $variation && $variation->image
//            ? $variation->image
//            : ($this->thumbnail ?? null);
//
//        $regular_product_discount = $on_sale && !empty($sale_price)
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//        // CRITICAL: Only process buy-together for root products, NEVER for nested ones
//        $buy_it_together = null;
//        $buy_together = null;
//
//        if (!$is_nested && $buy_together_id) {
//            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
//                ->whereNotNull('wordpress_id')
//                ->where('status', 'publish')
//                ->first();
//
//            if ($buy_together) {
//                $buy_it_together = $this->processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang);
//            }
//        }
//
//        // Calculate the discount to apply to the main product
//        $buy_together_discount = 0;
//        if ($buy_it_together && isset($buy_it_together['main_product_discount'])) {
//            $buy_together_discount = (float)$buy_it_together['main_product_discount'];
//        } elseif ($main_product_discount > 0) {
//            $buy_together_discount = (float)$main_product_discount;
//        }
//
//        // Calculate base price from variation or product
//        $base_price = $variation_id && $variation
//            ? ($sale_price ?: ($regular_price ?: 0))
//            : ($this->resource['regular_price'] ?? 0);
//
//        $product_main_discount = $on_sale && !empty($sale_price)
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//        $total_discount = $buy_together_discount
//            ? $buy_together_discount + $product_main_discount
//            : $product_main_discount;
//
//        $price_after_discount = $base_price - ($base_price * ($total_discount / 100));
//        if (!$buy_together_id) {
//            $price_after_discount = $this->sale_price ?: $this->unit_price ?: $this->regular_price;
//        }
//
//        $data = [
//            'id' => $this->wordpress_id ?? null,
//            'name' => $this->name ?? null,
//            'category_ids' => json_decode($this->category_ids ?? '[]') ?? null,
//            'price_before_discount' => $regular_price,
//            'price_after_discount' => (integer) $price_after_discount,
//            'thumbnail' => $thumbnail,
//            'images' => $variation && $variation->image
//                ? [$variation->image]
//                : collect(json_decode($this->images ?? '[]', true))
//                    ->map(fn($image) => $image['src'] ?? null)
//                    ->filter()
//                    ->toArray(),
//            'in_wishlist' => auth()->check()
//                ? Wishlist::where('customer_id', auth()->user()->id)
//                    ->where(function($query) use ($variation_id) {
//                        $query->where('product_id', $this->wordpress_id ?? 0)
//                            ->orWhere('product_id', $this->product_id ?? 0);
//                    })
//                    ->exists()
//                : false,
//            'slug' => $this->slug ?? null,
//            'product_stock_count' => $current_stock,
//            'discount' => $product_main_discount,
//            'is_taxable' => (bool)($this->is_taxable ?? false),
//            'buy_together_id' => $is_nested ? null : $buy_together_id,
//            'buy_it_together' => $buy_it_together,
//            'main_product_discount' => (float) $main_product_discount,
//            'buy_together_discount' => (float) $product_discount,
//            'order_details_count' => $this->order_details_count ?? 0,
//        ];
//
//        if (auth()->check()) {
//            $userId = auth()->id();
//
//            if ($buy_together_id == null) {
//                if ($variation_id) {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where(function($query) {
//                            $query->where('product_id', @$this->wordpress_id)
//                                ->orWhere('product_id', @$this->translation_ar)
//                                ->orWhere('product_id', @$this->translation_en);
//                        })
//                        ->where('variant_id', $variation_id)
//                        ->whereNull('buy_together_id')
//                        ->first()->quantity ?: 0;
//                } else {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where(function($query) {
//                            $query->where('product_id', @$this->wordpress_id)
//                                ->orWhere('product_id', @$this->translation_ar)
//                                ->orWhere('product_id', @$this->translation_en);
//                        })
//                        ->whereNull('variant_id')
//                        ->whereNull('buy_together_id')
//                        ->first()->quantity ?: 0;
//                }
//            } elseif ($buy_together_id != null) {
//                if ($variation_id) {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where(function($query) {
//                            $query->where('product_id', @$this->wordpress_id)
//                                ->orWhere('product_id', @$this->translation_ar)
//                                ->orWhere('product_id', @$this->translation_en);
//                        })
//                        ->where('variant_id', $variation_id)
//                        ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%')
//                        ->first()->quantity ?: 0;
//                } else {
//                    $data['cart_count'] = @CartProduct::where('customer_id', $userId)
//                        ->where(function($query) {
//                            $query->where('product_id', @$this->wordpress_id)
//                                ->orWhere('product_id', @$this->translation_ar)
//                                ->orWhere('product_id', @$this->translation_en);
//                        })
//                        ->where('buy_together_id', $buy_together_id)
//                        ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%')
//                        ->first()->quantity ?: 0;
//                }
//            }
//        } else {
//            $data['cart_count'] = 0;
//        }
//
//        $data['variant'] = $variation_id != null && $variation
//            ? DigitalProductVariationResource::make($variation)->additional([
//                'lang' => $lang, // Pass language to variation resource
//            ])
//            : null;
//
//        $data['variants'] = $variation_id == null
//            ? DigitalProductVariationResource::collection($this->digitalVariation ?? [])->additional(['lang' => $lang])
//            : [];
//        return $data;
//    }

//    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang)
//    {
//        $product_ids = json_decode($selected_buy_together_ids);
//
//        if (!is_array($product_ids) || empty($product_ids)) {
//            return null;
//        }
//
//        $products_list = collect();
//        $product_discount = (float)($buy_together->woodmart_fbt_product_discount ?? 0);
//        $main_product_discount = (float)($buy_together->woodmart_main_products_discount ?? 0);
//
//        // First, check which IDs are variations and which are products
//        $buy_together_variations = DigitalProductVariation::whereIn('wordpress_id', $product_ids)->get();
//        $variation_ids = $buy_together_variations->pluck('wordpress_id')->toArray();
//
//        // Get product IDs that are NOT variations
//        $product_only_ids = array_diff($product_ids, $variation_ids);
//        $buy_together_products = Product::whereIn('wordpress_id', $product_only_ids)
//            ->where('status', 1)
//            ->where('current_stock', '>=', 1)
//            ->get();
//
//        // Process standalone products (not variations)
//        foreach ($buy_together_products as $product) {
//            // Skip if this is the current product
//            if ($product->wordpress_id == $this->wordpress_id) {
//                continue;
//            }
//
//            // Handle language translation for buy together product
//            if ($product->lang != $lang) {
//                $translationField = "translation_{$lang}";
//                $translationId = $product->$translationField ?? null;
//
//                if ($translationId) {
//                    $translatedProduct = Product::where('wordpress_id',$translationId)->first();
//                    if ($translatedProduct) {
//                        $product = $translatedProduct;
//                    }
//                }
//            }
//
//            $products_list->push(
//                productArrivalResource::make($product)->additional([
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'product_main_discount' => (float) ($main_product_discount + $regular_product_discount),
//                    'buy_together_id' => null,
//                    'is_nested' => true,
//                    'lang' => $lang, // Pass language to nested resource
//                ])
//            );
//        }
//
//        // Process variations
//        foreach ($buy_together_variations as $variation) {
//            // Skip if the variation belongs to the current product
//            if ($variation->product_id == $this->wordpress_id) {
//                continue;
//            }
//
//            // Get the parent product for this variation
//            $parent_product = Product::where('wordpress_id', $variation->product_id)->first();
//
//            if ($parent_product) {
//                // Handle language translation for parent product
//                if ($parent_product->lang != $lang) {
//                    $translationField = "translation_{$lang}";
//                    $translationId = $parent_product->$translationField ?? null;
//
//                    if ($translationId) {
//                        $translatedParentProduct = Product::where('wordpress_id',$translationId)->first();
//                        if ($translatedParentProduct) {
//                            $parent_product = $translatedParentProduct;
//                        }
//                    }
//                }
//
//                $products_list->push(
//                    productArrivalResource::make($parent_product)->additional([
//                        'variation_id' => $variation->wordpress_id,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => (float)($main_product_discount ?: $regular_product_discount),
//                        'buy_together_id' => null,
//                        'is_nested' => true,
//                        'lang' => $lang, // Pass language to nested resource
//                    ])
//                );
//            }
//        }
//
//        if ($products_list->isEmpty()) {
//            return null;
//        }
//
//        return [
//            'buy_together_id' => $buy_together->wordpress_id,
//            'main_product_discount' => (float)($buy_together->woodmart_main_products_discount ?: $regular_product_discount ?: 0),
//            'product_discount' => $product_discount,
//            '_woodmart_fbt_products' => $products_list->values()->toArray()
//        ];
//    }







//    public function toArray(Request $request): array
//    {
//        $lang = $this->additional['lang'] ?? 'en';
//        $variation_id = $this->additional['variation_id'] ?? null;
//        $product_discount = (float)($this->additional['product_discount'] ?? null);
//        $main_product_discount = (float)($this->additional['main_product_discount'] ?? null);
//        $buy_together_id = $this->additional['buy_together_id'] ?? null;
//        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
//
//        $is_nested = $this->additional['is_nested'] ?? false;
//
//        // Handle translation
//        $product = $this->resource;
//        if ($product && $product->lang != $lang) {
//            $translationField = "translation_{$lang}";
//            $translationId = $product->$translationField ?? null;
//
//            if ($translationId) {
//                $translatedProduct = Product::where('wordpress_id', $translationId)->first();
//                if ($translatedProduct) {
//                    $product = $translatedProduct;
//                    $this->resource = $translatedProduct;
//                }
//            }
//        }
//
//        // Get variation
//        $variation = $variation_id
//            ? DigitalProductVariation::where('wordpress_id', $variation_id)->first()
//            : null;
//
//        // Prices
//        $regular_price = $variation ? (float)$variation->regular_price : (float)($product->regular_price ?? 0);
//        $sale_price = $variation ? (float)$variation->sale_price : (float)($product->sale_price ?? 0);
//        $on_sale = $variation ? (bool)$variation->on_sale : (bool)($product->on_sale ?? false);
//
//        $current_stock = $variation
//            ? ($variation->stock_status === 'instock' ? 999 : 0)
//            : (int)($product->current_stock ?? 0);
//
//        $thumbnail = $variation && $variation->image ? $variation->image : $product->thumbnail;
//
//        $regular_product_discount = $on_sale && $sale_price
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//        // Buy together processing
//        $buy_it_together = null;
//        if (!$is_nested && $buy_together_id) {
//            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
//                ->where('status', 'publish')
//                ->first();
//
//            if ($buy_together) {
//                $buy_it_together = $this->processBuyTogether(
//                    $selected_buy_together_ids,
//                    $buy_together,
//                    $regular_product_discount,
//                    $lang
//                );
//            }
//        }
//
//        // Final discount
//        $buy_together_discount = $buy_it_together['main_product_discount'] ?? $main_product_discount ?? 0;
//
//        $base_price = $variation
//            ? ($sale_price ?: $regular_price)
//            : ($product->regular_price ?? 0);
//
//        $product_main_discount = $regular_product_discount;
//
//        $total_discount = $buy_together_discount
//            ? $buy_together_discount + $product_main_discount
//            : $product_main_discount;
//
//        $price_after_discount = $base_price - ($base_price * ($total_discount / 100));
//
//        // Override if normal product (no buy together)
//        if (!$buy_together_id) {
//            $price_after_discount = $product->sale_price ?: $product->unit_price ?: $product->regular_price;
//        }
//
//        // Base data
//        $data = [
//            'id' => $product->wordpress_id,
//            'name' => $product->name,
//            'category_ids' => json_decode($product->category_ids ?? '[]'),
//            'price_before_discount' => $regular_price,
//            'price_after_discount' => (int)$price_after_discount,
//            'thumbnail' => $thumbnail,
//            'images' => $variation && $variation->image
//                ? [$variation->image]
//                : collect(json_decode($product->images ?? '[]', true))
//                    ->pluck('src')
//                    ->filter()
//                    ->toArray(),
//            'in_wishlist' => auth()->check()
//                ? Wishlist::where('customer_id', auth()->id())
//                    ->where(function ($q) use ($product) {
//                        $q->where('product_id', $product->wordpress_id)
//                            ->orWhere('product_id', $product->translation_ar)
//                            ->orWhere('product_id', $product->translation_en);
//                    })
//                    ->exists()
//                : false,
//            'slug' => $product->slug,
//            'product_stock_count' => $current_stock,
//            'discount' => $product_main_discount,
//            'is_taxable' => (bool)$product->is_taxable,
//            'buy_together_id' => $is_nested ? null : $buy_together_id,
//            'buy_it_together' => $buy_it_together,
//            'main_product_discount' => (float)$main_product_discount,
//            'buy_together_discount' => (float)$product_discount,
//            'order_details_count' => $product->order_details_count ?? 0,
//        ];
//
//        // Cart count
//        if (auth()->check()) {
//            $userId = auth()->id();
//
//            $query = CartProduct::where('customer_id', $userId)
//                ->where(function ($q) use ($product) {
//                    $q->where('product_id', $product->wordpress_id)
//                        ->orWhere('product_id', $product->translation_ar)
//                        ->orWhere('product_id', $product->translation_en);
//                });
//
//            if ($variation_id) {
//                $query->where('variant_id', $variation_id);
//            } else {
//                $query->whereNull('variant_id');
//            }
//
//            if ($buy_together_id) {
//                $query->where('buy_together_id', $buy_together_id)
//                    ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%');
//            } else {
//                $query->whereNull('buy_together_id');
//            }
//
//            $data['cart_count'] = optional($query->first())->quantity ?? 0;
//        } else {
//            $data['cart_count'] = 0;
//        }
//
//        // Variants
//        $data['variant'] = $variation
//            ? DigitalProductVariationResource::make($variation)->additional(['lang' => $lang])
//            : null;
//
//        $data['variants'] = !$variation_id
//            ? DigitalProductVariationResource::collection($product->digitalVariation ?? [])->additional(['lang' => $lang])
//            : [];
//
//        return $data;
//    }
//
//    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang)
//    {
//        $ids = json_decode($selected_buy_together_ids);
//
//        if (!is_array($ids) || empty($ids)) {
//            return null;
//        }
//
//        $products_list = collect();
//
//        $product_discount = (float)$buy_together->woodmart_fbt_product_discount;
//        $main_product_discount = (float)$buy_together->woodmart_main_products_discount;
//
//        $variations = DigitalProductVariation::whereIn('wordpress_id', $ids)->get();
//        $variation_ids = $variations->pluck('wordpress_id')->toArray();
//
//        $product_ids = array_diff($ids, $variation_ids);
//
//        $products = Product::whereIn('wordpress_id', $product_ids)
//            ->where('status', 1)
//            ->where('current_stock', '>=', 1)
//            ->get();
//
//        // Standalone products
//        foreach ($products as $product) {
//            if ($product->wordpress_id == $this->wordpress_id) continue;
//
//            // Translate if needed
//            if ($product->lang != $lang) {
//                $tField = "translation_{$lang}";
//                $tId = $product->$tField;
//
//                if ($tId) {
//                    $translated = Product::where('wordpress_id', $tId)->first();
//                    if ($translated) $product = $translated;
//                }
//            }
//
//            $products_list->push(
//                productArrivalResource::make($product)->additional([
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'product_main_discount' => $main_product_discount + $regular_product_discount,
//                    'buy_together_id' => null,
//                    'is_nested' => true,
//                    'lang' => $lang,
//                ])
//            );
//        }
//
//        // Variation products
//        foreach ($variations as $variation) {
//            if ($variation->product_id == $this->wordpress_id) continue;
//
//            $parent = Product::where('wordpress_id', $variation->product_id)->first();
//
//            if ($parent) {
//                if ($parent->lang != $lang) {
//                    $tField = "translation_{$lang}";
//                    $tId = $parent->$tField;
//
//                    if ($tId) {
//                        $translated = Product::where('wordpress_id', $tId)->first();
//                        if ($translated) $parent = $translated;
//                    }
//                }
//
//                $products_list->push(
//                    productArrivalResource::make($parent)->additional([
//                        'variation_id' => $variation->wordpress_id,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => $main_product_discount ?: $regular_product_discount,
//                        'buy_together_id' => null,
//                        'is_nested' => true,
//                        'lang' => $lang,
//                    ])
//                );
//            }
//        }
//
//        if ($products_list->isEmpty()) {
//            return null;
//        }
//
//        return [
//            'buy_together_id' => $buy_together->wordpress_id,
//            'main_product_discount' => $main_product_discount ?: $regular_product_discount,
//            'product_discount' => $product_discount,
//            '_woodmart_fbt_products' => $products_list->values()->toArray(),
//        ];
//    }




//    public function toArray(Request $request): array
//    {
//        $lang = $this->additional['lang'] ?? 'en';
//        $variation_id = $this->additional['variation_id'] ?? null;
//        $product_discount = (float)($this->additional['product_discount'] ?? null);
//        $main_product_discount = (float)($this->additional['main_product_discount'] ?? null);
//        $buy_together_id = $this->additional['buy_together_id'] ?? null;
//        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
//        $is_nested = $this->additional['is_nested'] ?? false;
//
//
//        $product = $this->resolveProductByLang($this->resource, $lang);
//
//
//        $variation = $variation_id
//            ? DigitalProductVariation::where('wordpress_id', $variation_id)->first()
//            : null;
//
//
//        $regular_price = $variation ? (float)$variation->regular_price : (float)($product->regular_price ?? 0);
//        $sale_price = $variation ? (float)$variation->sale_price : (float)($product->sale_price ?? 0);
//        $on_sale = $variation ? (bool)$variation->on_sale : (bool)($product->on_sale ?? false);
//
//        $current_stock = $variation
//            ? ($variation->stock_status === 'instock' ? 999 : 0)
//            : (int)($product->current_stock ?? 0);
//
//        $thumbnail = $variation && $variation->image ? $variation->image : $product->thumbnail;
//
//        $regular_product_discount = $on_sale && $sale_price
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//
//        $buy_it_together = null;
//
//        if (!$is_nested && $buy_together_id) {
//            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
//                ->where('status', 'publish')
//                ->first();
//
//            if ($buy_together) {
//                $buy_it_together = $this->processBuyTogether(
//                    $selected_buy_together_ids,
//                    $buy_together,
//                    $regular_product_discount,
//                    $lang
//                );
//            }
//        }
//
//
//        $buy_together_discount = $buy_it_together['main_product_discount'] ?? $main_product_discount ?? 0;
//
//        $base_price = $variation
//            ? ($sale_price ?: $regular_price)
//            : ($product->regular_price ?? 0);
//
//        $product_main_discount = $regular_product_discount;
//
//        $total_discount = $buy_together_discount
//            ? $buy_together_discount + $product_main_discount
//            : $product_main_discount;
//
//        $price_after_discount = $base_price - ($base_price * ($total_discount / 100));
//
//        if (!$buy_together_id) {
//            $price_after_discount = $product->sale_price ?: $product->unit_price ?: $product->regular_price;
//        }
//
//
//        $data = [
//            'id' => $product->wordpress_id,
//            'name' => $product->name,
//            'category_ids' => json_decode($product->category_ids ?? '[]'),
//            'price_before_discount' => $regular_price,
//            'price_after_discount' => (int)$price_after_discount,
//            'thumbnail' => $thumbnail,
//
//            'images' => $variation && $variation->image
//                ? [$variation->image]
//                : collect(json_decode($product->images ?? '[]', true))
//                    ->pluck('src')
//                    ->filter()
//                    ->toArray(),
//
//            /** Wishlist based on any language */
//            'in_wishlist' => auth()->check()
//                ? Wishlist::where('customer_id', auth()->id())
//                    ->where(function ($q) use ($product) {
//                        $q->where('product_id', $product->wordpress_id)
//                            ->orWhere('product_id', $product->translation_ar)
//                            ->orWhere('product_id', $product->translation_en);
//                    })
//                    ->exists()
//                : false,
//
//            'slug' => $product->slug,
//            'product_stock_count' => $current_stock,
//            'discount' => $product_main_discount,
//            'is_taxable' => (bool)$product->is_taxable,
//
//            'buy_together_id' => $is_nested ? null : $buy_together_id,
//            'buy_it_together' => $buy_it_together,
//
//            'main_product_discount' => (float)$main_product_discount,
//            'buy_together_discount' => (float)$product_discount,
//            'order_details_count' => $product->order_details_count ?? 0,
//        ];
//
//
//        if (auth()->check()) {
//
//            $query = CartProduct::where('customer_id', auth()->id())
//                ->where(function ($q) use ($product) {
//                    $q->where('product_id', $product->wordpress_id)
//                        ->orWhere('product_id', $product->translation_ar)
//                        ->orWhere('product_id', $product->translation_en);
//                });
//
//            if ($variation_id) {
//                $query->where('variant_id', $variation_id);
//            } else {
//                $query->whereNull('variant_id');
//            }
//
//            if ($buy_together_id) {
//                $query->where('buy_together_id', $buy_together_id)
//                    ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%');
//            } else {
//                $query->whereNull('buy_together_id');
//            }
//
//            $data['cart_count'] = optional($query->first())->quantity ?? 0;
//        } else {
//            $data['cart_count'] = 0;
//        }
//
//
//        $data['variant'] = $variation
//            ? DigitalProductVariationResource::make($variation)->additional(['lang' => $lang])
//            : null;
//
//        $data['variants'] = !$variation_id
//            ? DigitalProductVariationResource::collection($product->digitalVariation ?? [])->additional(['lang' => $lang])
//            : [];
//
//        return $data;
//    }
//
//
//
//    private function resolveProductByLang($product, $lang)
//    {
//        if (!$product) return null;
//
//        if ($product->lang == $lang) {
//            return $product; // Already correct language
//        }
//
//        $translation_field = "translation_{$lang}";
//        $translation_id = $product->$translation_field;
//
//        if (!$translation_id) {
//            return $product; // No translation → keep original
//        }
//
//        $translated = Product::where('wordpress_id', $translation_id)->first();
//
//        return $translated ?: $product;
//    }
//
//
//
//    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang)
//    {
//        $ids = json_decode($selected_buy_together_ids);
//
//        if (!is_array($ids) || empty($ids)) {
//            return null;
//        }
//
//        $products_list = collect();
//
//        $product_discount = (float)$buy_together->woodmart_fbt_product_discount;
//        $main_product_discount = (float)$buy_together->woodmart_main_products_discount;
//
//        $variations = DigitalProductVariation::whereIn('wordpress_id', $ids)->get();
//        $variation_ids = $variations->pluck('wordpress_id')->toArray();
//
//        $product_ids = array_diff($ids, $variation_ids);
//
//        $products = Product::whereIn('wordpress_id', $product_ids)
//            ->where('status', 1)
//            ->where('current_stock', '>=', 1)
//            ->get();
//
//        /* Standalone products */
//        foreach ($products as $product) {
//            if ($product->wordpress_id == $this->wordpress_id) continue;
//
//            $product = $this->resolveProductByLang($product, $lang);
//
//            $products_list->push(
//                productArrivalResource::make($product)->additional([
//                    'variation_id' => null,
//                    'product_discount' => $product_discount,
//                    'product_main_discount' => $main_product_discount + $regular_product_discount,
//                    'buy_together_id' => null,
//                    'is_nested' => true,
//                    'lang' => $lang,
//                ])
//            );
//        }
//
//        /* Variation products */
//        foreach ($variations as $variation) {
//
//            if ($variation->product_id == $this->wordpress_id) continue;
//
//            $parent = Product::where('wordpress_id', $variation->product_id)->first();
//
//            if ($parent) {
//
//                $parent = $this->resolveProductByLang($parent, $lang);
//
//                $products_list->push(
//                    productArrivalResource::make($parent)->additional([
//                        'variation_id' => $variation->wordpress_id,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => $main_product_discount ?: $regular_product_discount,
//                        'buy_together_id' => null,
//                        'is_nested' => true,
//                        'lang' => $lang,
//                    ])
//                );
//            }
//        }
//
//        if ($products_list->isEmpty()) {
//            return null;
//        }
//
//        return [
//            'buy_together_id' => $buy_together->wordpress_id,
//            'main_product_discount' => $main_product_discount ?: $regular_product_discount,
//            'product_discount' => $product_discount,
//            '_woodmart_fbt_products' => $products_list->values()->toArray(),
//        ];
//    }




    public function toArray(Request $request): array
    {
//        $woodmart_product_main_discount=$this->additional['product_main_discount'] ?? null;
        $lang = $this->additional['lang'] ?? 'en';
//        dd($lang);
        $variation_id = $this->additional['variation_id'] ?? null;
        $product_discount = (float)($this->additional['product_discount'] ?? null);
        $main_product_discount = (float)($this->additional['main_product_discount'] ?? null);
        $buy_together_id = $this->additional['buy_together_id'] ?? null;
        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
        $is_nested = $this->additional['is_nested'] ?? false;


        $lang = $this->additional['lang'] ?? 'en';
        $isNested = $this->additional['is_nested'] ?? false;

        // For main product listings (home page), use strict language resolution
        // For cart/orders, allow translation fallback
        $isCartOrOrderContext = isset($this->additional['variation_id']) ||
            isset($this->additional['buy_together_id']) ||
            $isNested;

        if ($isCartOrOrderContext) {
            // In cart/order context, try to get translation but fallback to original
            $product = $this->resolveProductByLangStrict($this->resource, $lang);
            if (!$product) {
                $product = $this->resource;
            }
        } else {
            // In listing context (home page), product should already be in correct language
            // Just verify it matches the requested language
            if ($this->resource->lang != $lang) {
                // This shouldn't happen with the updated home page query
                $product = $this->resolveProductByLangStrict($this->resource, $lang) ?? $this->resource;
            } else {
                $product = $this->resource;
            }
        }

        // Resolve product STRICTLY in the requested language
//        $product = $this->resolveProductByLangStrict($this->resource, $lang);

        // If no translation exists, fallback to original (optional: you may throw/log instead)
        if (!$product) {
            $product = $this->resource;
        }

         $variation = $variation_id
            ? DigitalProductVariation::where('wordpress_id', $variation_id)->first()
            : null;

        $regular_price = $variation ? (float)$variation->regular_price : (float)($product->regular_price ?? 0);
        $sale_price = $variation ? (float)$variation->sale_price : (float)($product->sale_price ?? 0);
        $on_sale = $variation ? (bool)$variation->on_sale : (bool)($product->on_sale ?? false);

        $current_stock = $variation
            ? ($variation->stock_status === 'instock' ? 999 : 0)
            : (int)($product->current_stock ?? 0);

        $thumbnail = $variation && $variation->image ? $variation->image : $product->thumbnail;

        $regular_product_discount = $on_sale && $sale_price
            ? ((float)($regular_price - $sale_price) / $regular_price) * 100
            : 0;

        $buy_it_together = null;

        if (!$is_nested && $buy_together_id) {
            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
                ->where('status', 'publish')
                ->first();

            if ($buy_together) {
                $buy_it_together = $this->processBuyTogether(
                    $selected_buy_together_ids,
                    $buy_together,
                    $regular_product_discount,
                    $lang
                );
            }
        }

        $buy_together_discount = (float)(($buy_it_together['main_product_discount'] ?? $main_product_discount) ?? 0);

        $base_price = $variation
            ? ((float)$sale_price ?: (float)$regular_price)
            : (float)(($product->sale_price ?: $product->regular_price ?? 0));

        $product_main_discount = (float)$regular_product_discount;

        $total_discount = (float)$buy_together_discount
            ? (float)$buy_together_discount + (float)$product_main_discount
            : (float)$product_main_discount;

        $price_after_discount = $base_price;

        if (!$buy_together_id) {
            (float)$price_after_discount = (float)$product->sale_price ?: (float)$product->unit_price ?: (float)$product->regular_price;
        }

        // Prepare base product data (language-specific)
        $data = [
            'id' => $product->wordpress_id,
            'name' => $product->name,
            'category_ids' => json_decode($product->category_ids ?? '[]'),
            'price_before_discount' => (int)$regular_price,
            'price_after_discount' => (int)$price_after_discount,
            'thumbnail' => $thumbnail,

            'images' => $variation && $variation->image
                ? [$variation->image]
                : collect(json_decode($product->images ?? '[]', true))
                    ->pluck('src')
                    ->filter()
                    ->toArray(),

            // 👇 Wishlist: match ANY translation (AR/EN)
            'in_wishlist' => auth()->check()
                ? Wishlist::where('customer_id', auth()->id())
                    ->where(function ($q) use ($product) {
                        $ids = array_filter([
                            $product->wordpress_id,
                            $product->translation_ar ?? null,
                            $product->translation_en ?? null,
                        ]);
                        $q->whereIn('product_id', $ids);
                    })
                    ->exists()
                : false,

            'slug' => $product->slug,
            'product_stock_count' => $current_stock,
            'discount' => $product_main_discount,
            'is_taxable' => (bool)$product->is_taxable,

            'buy_together_id' => $is_nested ? null : $buy_together_id,
            'buy_it_together' => $buy_it_together,

            'main_product_discount' => (float)$main_product_discount,
            'buy_together_discount' => (float)$product_discount,
            'order_details_count' => $product->order_details_count ?? 0,
        ];

        // 👇 Cart: match ANY translation + variation + buy_together context
        if (auth()->check()) {
            $ids = array_filter([
                $product->wordpress_id,
                $product->translation_ar ?? null,
                $product->translation_en ?? null,
            ]);

            $query = CartProduct::where('customer_id', auth()->id())
                ->whereIn('product_id', $ids);

            if ($variation_id) {
                $query->where('variant_id', $variation_id);
            } else {
                $query->whereNull('variant_id');
            }

            if ($buy_together_id && $selected_buy_together_ids) {
                $query->where('buy_together_id', $buy_together_id)
                    ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%');
            } else {
                $query->whereNull('buy_together_id');
            }

            $data['cart_count'] = optional($query->first())->quantity ?? 0;
        } else {
            $data['cart_count'] = 0;
        }

        $data['variant'] = $variation
            ? DigitalProductVariationResource::make($variation)->additional(['lang' => $lang])
            : null;

        $data['variants'] = !$variation_id
            ? DigitalProductVariationResource::collection($product->digitalVariation ?? [])->additional(['lang' => $lang])
            : [];

        return $data;
    }

    /**
     * Resolve product STRICTLY in the given language.
     * Returns null if no translation exists — caller must handle fallback if needed.
     */
//    private function resolveProductByLangStrict($product, $lang)
//    {
//        if (!$product) {
//            return null;
//        }
//
//        // Already in correct language
//        if ($product->lang == $lang) {
//            return $product;
//        }
//
//        // Try to load translation
//        $translation_id = $product->{"translation_{$lang}"};
//        if ($translation_id) {
//            return Product::where('wordpress_id', $translation_id)
//                ->where('lang', $lang)
//                ->first();
//        }
//
//        return null; // No valid translation in target language
//    }
    private function resolveProductByLangStrict($product, $lang)
    {
        if (!$product) {
            return null;
        }

        // If product is already in the requested language, return it
        if ($product->lang == $lang) {
            return $product;
        }

        // Try to load translation ONLY for cart/order context
        $translation_id = $product->{"translation_{$lang}"};
        if ($translation_id) {
            $translatedProduct = Product::where('wordpress_id', $translation_id)
                ->where('lang', $lang)
                ->first();

            // If translation exists and is published, return it
            if ($translatedProduct && $translatedProduct->status == 1) {
                return $translatedProduct;
            }
        }

        // For cart/orders: return original product if no translation exists
        // For home page: this case shouldn't happen due to language filtering
        return $product;
    }
    /**
     * Process "Buy Together" products — uses strict resolver for nested items too.
     */
    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang)
    {
        $ids = json_decode($selected_buy_together_ids, true);

        if (!is_array($ids) || empty($ids)) {
            return null;
        }

        $products_list = collect();

//        $product_discount = (float)$buy_together->woodmart_fbt_product_discount;
        $main_product_discount = (float)$buy_together->woodmart_main_products_discount;

        $variations = DigitalProductVariation::whereIn('wordpress_id', $ids)->get();
        $variation_ids = $variations->pluck('wordpress_id')->toArray();
        $product_ids = array_diff($ids, $variation_ids);

        // Load standalone products
        $products = Product::whereIn('wordpress_id', $product_ids)
            ->where('status', 1)
            ->where('current_stock', '>=', 1)
            ->get();

        foreach ($products as $product) {
            // Skip if it's the main product itself
            if ($product->wordpress_id == $this->resource->wordpress_id) {
                continue;
            }

            // Resolve in current language (strict)
            $resolved = $this->resolveProductByLangStrict($product, $lang);
            if (!$resolved) {
                continue; // Skip if no translation in current lang
            }

            $products_list->push(
                productArrivalResource::make($resolved)->additional([
                    'variation_id' => null,
//                    'product_discount' => $product_discount,
                    'product_main_discount' => (float)$main_product_discount + (float)$regular_product_discount,
                    'buy_together_id' => null,
                    'is_nested' => true,
                    'lang' => $lang,
                ])
            );
        }

        // Load variation-based products
        foreach ($variations as $variation) {
            if ($variation->product_id == $this->resource->wordpress_id) {
                continue;
            }

            $parent = Product::where('wordpress_id', $variation->product_id)->first();
            if (!$parent) {
                continue;
            }

            $resolvedParent = $this->resolveProductByLangStrict($parent, $lang);
            if (!$resolvedParent) {
                continue; // Skip if parent has no translation in current lang
            }

            $products_list->push(
                productArrivalResource::make($resolvedParent)->additional([
                    'variation_id' => $variation->wordpress_id,
//                    'product_discount' => $product_discount,
                    'product_main_discount' => (float)$main_product_discount ?: (float)$regular_product_discount,
                    'buy_together_id' => null,
                    'is_nested' => true,
                    'lang' => $lang,
                ])
            );
        }

        if ($products_list->isEmpty()) {
            return null;
        }

        return [
            'buy_together_id' => $buy_together->wordpress_id,
            'main_product_discount' => (float)$main_product_discount ?: (float)$regular_product_discount,
//            'product_discount' => $product_discount,
            '_woodmart_fbt_products' => $products_list->values()->toArray(),
        ];
    }


//    public function toArray(Request $request): array
//    {
//        $lang = $this->additional['lang'] ?? 'en';
//        $variation_id = $this->additional['variation_id'] ?? null;
//        $product_discount = (float)($this->additional['product_discount'] ?? null);
//        $main_product_discount = (float)($this->additional['main_product_discount'] ?? null);
//        $buy_together_id = $this->additional['buy_together_id'] ?? null;
//        $selected_buy_together_ids = $this->additional['selected_buy_together_ids'] ?? null;
//        $is_nested = $this->additional['is_nested'] ?? false;
//
////        $product = $this->resolveProductByLang($this->resource, $lang);
//        $product = $this->resolveProductByLang($this->resource, $lang, true);
////        dd($product);
//
//        // Get variation in the same language as the product
//        $variation = null;
//        if ($variation_id) {
//            // Find variation that belongs to the resolved product (which is in correct language)
//            $variation = DigitalProductVariation::where('wordpress_id', $variation_id)
//                ->where('product_id', $product->wordpress_id) // Ensure variation belongs to current language product
//                ->first();
//
//            // If no variation found for this language product, try to find by original resource ID
//            if (!$variation && $this->resource->wordpress_id != $product->wordpress_id) {
//                $original_variation = DigitalProductVariation::where('wordpress_id', $variation_id)->first();
//                if ($original_variation) {
//                    // Find the corresponding variation for the translated product
//                    $variation = DigitalProductVariation::where('product_id', $product->wordpress_id)
//                        ->where('name', $original_variation->name) // or another unique identifier
//                        ->first();
//                }
//            }
//        }
//
//        $regular_price = $variation ? (float)$variation->regular_price : (float)($product->regular_price ?? 0);
//        $sale_price = $variation ? (float)$variation->sale_price : (float)($product->sale_price ?? 0);
//        $on_sale = $variation ? (bool)$variation->on_sale : (bool)($product->on_sale ?? false);
//
//        $current_stock = $variation
//            ? ($variation->stock_status === 'instock' ? 999 : 0)
//            : (int)($product->current_stock ?? 0);
//
//        $thumbnail = $variation && $variation->image ? $variation->image : @$product->thumbnail;
//
//        $regular_product_discount = $on_sale && $sale_price
//            ? round((($regular_price - $sale_price) / $regular_price) * 100)
//            : 0;
//
//        $buy_it_together = null;
//
//        if (!$is_nested && $buy_together_id) {
//            $buy_together = BuyItTogether::where('wordpress_id', $buy_together_id)
//                ->where('status', 'publish')
//                ->first();
//
//            if ($buy_together) {
//                $buy_it_together = $this->processBuyTogether(
//                    $selected_buy_together_ids,
//                    $buy_together,
//                    $regular_product_discount,
//                    $lang
//                );
//            }
//        }
//
//        $buy_together_discount = $buy_it_together['main_product_discount'] ?? $main_product_discount ?? 0;
//
//        $base_price = $variation
//            ? ((int)$sale_price ?: (int)$regular_price)
//            : ((int)@$product->regular_price ?? 0);
//
//        $product_main_discount = $regular_product_discount;
//
//        $total_discount = $buy_together_discount
//            ? $buy_together_discount + $product_main_discount
//            : $product_main_discount;
//
//        $price_after_discount = (int)$base_price - ((int)$base_price * ((int)$total_discount / 100));
//
//        if (!$buy_together_id) {
//            $price_after_discount = (int)$product->sale_price ?: (int)$product->unit_price ?: (int)$product->regular_price;
//        }
//
//        $data = [
//            'id' => $product->wordpress_id,
//            'name' => $product->name,
//            'category_ids' => json_decode($product->category_ids ?? '[]'),
//            'price_before_discount' => $regular_price,
//            'price_after_discount' => (int)$price_after_discount,
//            'thumbnail' => $thumbnail,
//
//            'images' => $variation && $variation->image
//                ? [$variation->image]
//                : collect(json_decode($product->images ?? '[]', true))
//                    ->pluck('src')
//                    ->filter()
//                    ->toArray(),
//
//            /** Wishlist - handle both languages for cart purposes */
//            'in_wishlist' => auth()->check()
//                ? Wishlist::where('customer_id', auth()->id())
//                    ->where(function ($q) use ($product) {
//                        $q->where('product_id', $product->wordpress_id);
//                        // Only add translations if they exist and are different
//                        if ($product->translation_ar && $product->translation_ar != $product->wordpress_id) {
//                            $q->orWhere('product_id', $product->translation_ar);
//                        }
//                        if ($product->translation_en && $product->translation_en != $product->wordpress_id) {
//                            $q->orWhere('product_id', $product->translation_en);
//                        }
//                    })
//                    ->exists()
//                : false,
//
//            'slug' => $product->slug,
//            'product_stock_count' => $current_stock,
//            'discount' => $product_main_discount,
//            'is_taxable' => (bool)$product->is_taxable,
//
//            'buy_together_id' => $is_nested ? null : $buy_together_id,
//            'buy_it_together' => $buy_it_together,
//
//            'main_product_discount' => (float)$main_product_discount,
//            'buy_together_discount' => (float)$product_discount,
//            'order_details_count' => $product->order_details_count ?? 0,
//        ];
//
//        if (auth()->check()) {
//            $query = CartProduct::where('customer_id', auth()->id())
//                ->where(function ($q) use ($product) {
//                    $q->where('product_id', $product->wordpress_id);
//                    // Add translations only if we want to match across languages in cart
//                    if ($product->translation_ar && $product->translation_ar != $product->wordpress_id) {
//                        $q->orWhere('product_id', $product->translation_ar);
//                    }
//                    if ($product->translation_en && $product->translation_en != $product->wordpress_id) {
//                        $q->orWhere('product_id', $product->translation_en);
//                    }
//                });
//
//            if ($variation_id) {
//                $query->where('variant_id', $variation_id);
//            } else {
//                $query->whereNull('variant_id');
//            }
//
//            if ($buy_together_id) {
//                $query->where('buy_together_id', $buy_together_id)
//                    ->where('selected_buy_together_ids', 'like', '%' . $selected_buy_together_ids . '%');
//            } else {
//                $query->whereNull('buy_together_id');
//            }
//
//            $data['cart_count'] = optional($query->first())->quantity ?? 0;
//        } else {
//            $data['cart_count'] = 0;
//        }
//
//        $data['variant'] = $variation
//            ? DigitalProductVariationResource::make($variation)->additional(['lang' => $lang])
//            : null;
//
//        // Get variants for the current language product only
//        $data['variants'] = !$variation_id
//            ? DigitalProductVariationResource::collection(
//                $product->digitalVariation ?? []
//            )->additional(['lang' => $lang])
//            : [];
//
//        return $data;
//    }
//
////    private function resolveProductByLang($product, $lang)
////    {
////        if (!$product) return null;
////
////        if ($product->lang == $lang) {
////            return $product; // Already correct language
////        }
////
////        $translation_field = "translation_{$lang}";
////        $translation_id = $product->$translation_field;
////
////        if (!$translation_id) {
////            return $product; // No translation → keep original
////        }
////
////        $translated = Product::where('wordpress_id', $translation_id)->first();
////
////        return $translated ?: $product;
////    }
//
//
//
//
//    private function resolveProductByLang($product, $lang, $strict = true)
//    {
//        if (!$product) return null;
//
//        if ($product->lang == $lang) {
//            return $product; // Already correct language
//        }
//
//        $translation_field = "translation_{$lang}";
//        $translation_id = $product->$translation_field;
//
//        if (!$translation_id) {
//            return $strict ? null : $product;
//        }
//
//        $translated = Product::where('wordpress_id', $translation_id)->first();
//
//        return $translated ?: ($strict ? null : $product);
//    }
//
//    private function processBuyTogether($selected_buy_together_ids, $buy_together, $regular_product_discount, $lang)
//    {
//        $ids = json_decode($selected_buy_together_ids);
//
//        if (!is_array($ids) || empty($ids)) {
//            return null;
//        }
//
//        $products_list = collect();
//
//        $product_discount = (float)$buy_together->woodmart_fbt_product_discount;
//        $main_product_discount = (float)$buy_together->woodmart_main_products_discount;
//
//        // First, try to get variations that match the current language context
//        $variations = DigitalProductVariation::whereIn('wordpress_id', $ids)->get();
//        $variation_ids = $variations->pluck('wordpress_id')->toArray();
//
//        $product_ids = array_diff($ids, $variation_ids);
//
//        // For products, ensure we get the correct language version
//        $products = Product::whereIn('wordpress_id', $product_ids)
//            ->where('status', 1)
//            ->where('current_stock', '>=', 1)
//            ->get();
//
//        /* Standalone products - ensure correct language */
//        foreach ($products as $product) {
//            if ($product->wordpress_id == $this->wordpress_id) continue;
//
//            // Resolve to the correct language version
//            $resolved_product = $this->resolveProductByLang($product, $lang);
//
//            // Only add if this product is in the correct language
//            if ($resolved_product->lang == $lang) {
//                $products_list->push(
//                    productArrivalResource::make($resolved_product)->additional([
//                        'variation_id' => null,
//                        'product_discount' => $product_discount,
//                        'product_main_discount' => $main_product_discount + $regular_product_discount,
//                        'buy_together_id' => null,
//                        'is_nested' => true,
//                        'lang' => $lang,
//                    ])
//                );
//            }
//        }
//
//        /* Variation products - ensure correct language */
//        foreach ($variations as $variation) {
//            if ($variation->product_id == $this->wordpress_id) continue;
//
//            // Get the parent product and ensure it's in the correct language
//            $parent = Product::where('wordpress_id', $variation->product_id)->first();
//
//            if ($parent) {
//                $resolved_parent = $this->resolveProductByLang($parent, $lang);
//
//                // Only add if the resolved parent is in the correct language
//                if ($resolved_parent->lang == $lang) {
//                    $products_list->push(
//                        productArrivalResource::make($resolved_parent)->additional([
//                            'variation_id' => $variation->wordpress_id,
//                            'product_discount' => $product_discount,
//                            'product_main_discount' => $main_product_discount ?: $regular_product_discount,
//                            'buy_together_id' => null,
//                            'is_nested' => true,
//                            'lang' => $lang,
//                        ])
//                    );
//                }
//            }
//        }
//
//        if ($products_list->isEmpty()) {
//            return null;
//        }
//
//        return [
//            'buy_together_id' => $buy_together->wordpress_id,
//            'main_product_discount' => $main_product_discount ?: $regular_product_discount,
//            'product_discount' => $product_discount,
//            '_woodmart_fbt_products' => $products_list->values()->toArray(),
//        ];
//    }

}