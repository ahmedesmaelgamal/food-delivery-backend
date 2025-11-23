<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\DigitalProductVariation;
use App\Models\BuyItTogether;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RestAPI\v5\productArrivalResource;

class CartProductResource extends JsonResource
{
//    public function toArray(Request $request)
//    {
//
//        if (isset($this->buy_together_ids)) {
////            $data = [];
////            $buyTogether = BuyItTogether::where('id', $this->product_id)->first();
////            dd($buyTogether);
//
//            if (isset($this->buy_together_ids)) {
//                foreach (json_decode($this->buy_together_ids) as $buy_together) {
//                    if (Product::where('wordpress_id', $buy_together)->exists()) {
//                        if ($this->variation_id){
//                            $variation = DigitalProductVariation::where('wordpress_id', $buy_together)->first();
//                            if ($variation) {
//                                $product = Product::where('wordpress_id', $variation->product_id)->first();
//                                if ($product) {
//                                    $data =ProductArrivalResource::make($product)
//                                        ->additional(['variation_id' => $this->variation_id]);
//                                }
//                            }
//                        }else{
//                            dd('welcome');
//                            $product = Product::where('wordpress_id', $buy_together)->first();
//                            $data = productArrivalResource::make($product);
//                        }
//                    }
//                }
//            }
//        } else {
////            dd($this->variant_id);
//
//            $product = Product::where('wordpress_id', $this->product_id)->first();
//            if (isset($this->variant_id)){
//                $variation=DigitalProductVariation::where('wordpress_id',$this->variant_id)->first();
//                if (isset($variation)){
//                    $data =ProductArrivalResource::make($product)
//                        ->additional(['variation_id' => $this->variant_id]);
//                }else{
//                    return null;
//                }
//
//            }else{
//                $data = productArrivalResource::make($product);
//            }
////            'variation_id' => $variation_id?:$this->variation_id
//
//
////            dd('sdc',$data);
//






//    public function toArray(Request $request)
//    {
////        $data = [];
//
//        if (!empty($this->selected_buy_together_ids)) {
//                $lang = $this->additional['lang'] ?? null;
//
////            foreach (json_decode($this->selected_buy_together_ids) as $buy_together) {
//                $product = Product::where('wordpress_id', $this->product_id)->first();
////                $this->buy_together_id = $buy_together;
//                if ($product) {
//                    $buy_together_record=BuyItTogether::where('wordpress_id',$this->buy_together_id)->first();
//                    $product_discount = (float)($buy_together_record->woodmart_fbt_product_discount ?? null);
//                    $main_product_discount = (float)($buy_together_record->woodmart_main_products_discount ?? null);
//                    $buy_together_id = $buy_together_record->wordpress_id ?? null;
//                    $regular_product_discount = $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0;
////                    dd($buy_together_record,$product_discount,$main_product_discount,$buy_together_id,$regular_product_discount);
//
////                    if (!empty($this->variation_id)) {
////                        $variation = DigitalProductVariation::where('wordpress_id', $buy_together)->first();
//
////                        if ($variation) {
////                            $parentProduct = Product::where('wordpress_id', $variation->product_id)->first();
////
////
////
////                            // Get the parent product for the variation
//////                            $parent_product = Product::where('wordpress_id', $variation->product_id)->first();
////                            if ($parentProduct) {
//////                                $data[] = ProductArrivalResource::make($parentProduct)
//////                                    ->additional(['variation_id' => $this->variation_id])
//////                                    ->additional(['variation_id' => $this->variation_id]);
////
////                                $data[] =productArrivalResource::make($parentProduct)->additional([
////                                    'buy_together' => true,
////                                    'variation_id' => $variation->wordpress_id,
////                                    'product_discount' => $product_discount,
////                                    'buy_together_id' => $buy_together_id,
////                                    'product_main_discount' => (float)($main_product_discount?:($this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0) + $regular_product_discount)
////                                ]);
////
////
////                            }
////                        }$buy_together_record
////                    } else {
////                        $data[] = ProductArrivalResource::make($product)->additional(['variation_id' => $this->variation_id]);
////                    dd(json_decode($this->selected_buy_together_ids));
//
//                    if ($buy_together_id){
//                        $main_product_discount_calculated= (float)($main_product_discount + $regular_product_discount);
//                    }else{
//                        $main_product_discount_calculated= (float)$regular_product_discount;
//                    }
//                        $data =productArrivalResource::make($product)->additional([
//                            'buy_together' => true,
//                            'variation_id' => $this->variant_id?:null,
//                            'product_discount' => (float) $product_discount,
//                            'buy_together_id' => $buy_together_id,
//                            'selected_buy_together_ids' => $this->selected_buy_together_ids,
//                            'main_product_discount' => $main_product_discount_calculated,
//                            'is_cart_buy_together' => true,
//                        ]);
////                    }
//                }
////            }
//        } else {
//            $product = Product::where('wordpress_id', $this->product_id)->first();
//
//            if ($product) {
//
//                if (!empty($this->variant_id)) {
//                    $variation = DigitalProductVariation::where('wordpress_id', $this->variant_id)->first();
//
//                    if ($variation) {
//                        $data= ProductArrivalResource::make($product)
//                            ->additional(['variation_id' => $this->variant_id]);
//                    }
//                } else {
//                    $data= ProductArrivalResource::make($product);
//                }
//            }
//        }
//
//        // 🔹 Remove null items (if any slipped in)
////        $data = array_filter($data);
//
//        return $data;
//    }





//    public function toArray(Request $request)
//    {
////        dd($this->additional['lang']);
//        $lang = $this->additional['lang'] ?? 'en';
//
//        if (!empty($this->selected_buy_together_ids)) {
//            $product = Product::where('wordpress_id', $this->product_id)->first();
//
//            // Handle language translation for main product
//            if ($product && $product->lang != $lang) {
//                $translationField = "translation_{$lang}";
//                $translationId = $product->$translationField ?? null;
//
//                if ($translationId) {
//                    $translatedProduct = Product::find($translationId);
//                    if ($translatedProduct) {
//                        $product = $translatedProduct;
//                    }
//                }
//            }
//
//            if ($product) {
//                $buy_together_record = BuyItTogether::where('wordpress_id', $this->buy_together_id)->first();
//                $product_discount = (float)($buy_together_record->woodmart_fbt_product_discount ?? null);
//                $main_product_discount = (float)($buy_together_record->woodmart_main_products_discount ?? null);
//                $buy_together_id = $buy_together_record->wordpress_id ?? null;
//                $regular_product_discount = $this->resource['on_sale'] && !empty($this->resource['sale_price'])
//                    ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100)
//                    : 0;
//
//                if ($buy_together_id) {
//                    $main_product_discount_calculated = (float)($main_product_discount + $regular_product_discount);
//                } else {
//                    $main_product_discount_calculated = (float)$regular_product_discount;
//                }
//
//                $data = productArrivalResource::make($product)->additional([
//                    'buy_together' => true,
//                    'variation_id' => $this->variant_id ?: null,
//                    'product_discount' => (float) $product_discount,
//                    'buy_together_id' => $buy_together_id,
//                    'selected_buy_together_ids' => $this->selected_buy_together_ids,
//                    'main_product_discount' => $main_product_discount_calculated,
//                    'is_cart_buy_together' => true,
//                    'lang' => $lang, // Pass language to ProductArrivalResource
//                ]);
//            }
//        } else {
//            $product = Product::where('wordpress_id', $this->product_id)->first();
//
//            // Handle language translation for main product
//            if ($product && $product->lang != $lang) {
//                $translationField = "translation_{$lang}";
//                $translationId = $product->$translationField ?? null;
//
//                if ($translationId) {
//                    $translatedProduct = Product::find($translationId);
//                    if ($translatedProduct) {
//                        $product = $translatedProduct;
//                    }
//                }
//            }
//
//            if ($product) {
//                if (!empty($this->variant_id)) {
//                    $variation = DigitalProductVariation::where('wordpress_id', $this->variant_id)->first();
//
//                    if ($variation) {
//                        $data = ProductArrivalResource::make($product)
//                            ->additional([
//                                'variation_id' => $this->variant_id,
//                                'lang' => $lang, // Pass language
//                            ]);
//                    }
//                } else {
//                    $data = ProductArrivalResource::make($product)
//                        ->additional(['lang' => $lang]); // Pass language
//                }
//            }
//        }
//
//        return $data ?? null;
//    }






    public function toArray(Request $request)
    {
        $lang = $this->additional['lang'] ?? 'en';

        // ✅ FIX: Always get the canonical product and then translate for display
        $canonicalProduct = $this->getCanonicalProduct($this->product_id);

        if (!$canonicalProduct) {
            return null;
        }

        // ✅ FIX: Translate the canonical product for display
        $displayProduct = $this->translateProductForDisplay($canonicalProduct, $lang);

        if (!empty($this->selected_buy_together_ids)) {
            $buy_together_record = BuyItTogether::where('wordpress_id', $this->buy_together_id)->first();
            $product_discount = (float)($buy_together_record->woodmart_fbt_product_discount ?? null);
            $main_product_discount = (float)($buy_together_record->woodmart_main_products_discount ?? null);
            $buy_together_id = $buy_together_record->wordpress_id ?? null;

            $regular_product_discount = $displayProduct->on_sale && !empty($displayProduct->sale_price)
                ? round((((float)$displayProduct->regular_price - (float)$displayProduct->sale_price) / (float)$displayProduct->regular_price) * 100)
                : 0;

            if ($buy_together_id) {
                $main_product_discount_calculated = (float)($main_product_discount + $regular_product_discount);
            } else {
                $main_product_discount_calculated = (float)$regular_product_discount;
            }

            $data = productArrivalResource::make($displayProduct)->additional([
                'buy_together' => true,
                'variation_id' => $this->variant_id ?: null,
                'product_discount' => (float) $product_discount,
                'buy_together_id' => $buy_together_id,
                'selected_buy_together_ids' => $this->selected_buy_together_ids,
                'main_product_discount' => $main_product_discount_calculated,
                'is_cart_buy_together' => true,
                'lang' => $lang,
            ]);
        } else {
            if (!empty($this->variant_id)) {
                $variation = DigitalProductVariation::where('wordpress_id', $this->variant_id)->first();

                if ($variation) {
                    $data = ProductArrivalResource::make($displayProduct)
                        ->additional([
                            'variation_id' => $this->variant_id,
                            'lang' => $lang,
                        ]);
                } else {
                    $data = ProductArrivalResource::make($displayProduct)
                        ->additional(['lang' => $lang]);
                }
            } else {
                $data = ProductArrivalResource::make($displayProduct)
                    ->additional(['lang' => $lang]);
            }
        }

        return $data ?? null;
    }

    /**
     * ✅ Get the canonical product (main product, not translation)
     */
    private function getCanonicalProduct($productId)
    {
        $product = Product::where('wordpress_id', $productId)->first();

        if (!$product) {
            return null;
        }

        // If this product has translations, it's a main product
        if ($product->translation_ar || $product->translation_en) {
            return $product;
        }

        // If this product doesn't have translations, check if it's a translation of another product
        $mainProduct = Product::where('translation_ar', $productId)
            ->orWhere('translation_en', $productId)
            ->first();

        return $mainProduct ?: $product;
    }

    /**
     * ✅ Translate product for display in the current language
     */
    private function translateProductForDisplay($product, $lang)
    {
        // If product is already in requested language, return it
        if ($product->lang == $lang) {
            return $product;
        }

        // Try to find translation
        $translationField = "translation_{$lang}";
        $translationId = $product->$translationField ?? null;

        if ($translationId) {
            $translatedProduct = Product::where('wordpress_id', $translationId)
                ->where('lang', $lang)
                ->first();

            if ($translatedProduct) {
                return $translatedProduct;
            }
        }

        // Return original product if no translation found
        return $product;
    }
}







//        $product_discount = (float)($this->additional['product_discount'] ?? 0);
        // Prepare common product data
//        $productData = [
//            'id' => $this->product_id,
//            'name' => $this->name,
//            'price' => $this->price,
//            'quantity' => $this->quantity,
//            'variation_id' => $variation_id,
//            'product_discount' => $product_discount,
//            'product_main_discount' => $main_product_discount,
//            'buy_together_id' => $buy_together_id,
//            'buy_together' => $buy_together,
//            'thumbnail' => $this->thumbnail,
//        ];


//        return $data ;
//    }
//}




//
//class CartProductResource extends JsonResource
//{
//    public function toArray(Request $request)
//    {
//        if (isset($this->buy_together_ids)) {
//            foreach (json_decode($this->buy_together_ids) as $buy_together) {
//                if (Product::where('wordpress_id', $buy_together)->exists()) {
//                    if ($this->variation_id) {
//                        $variation = DigitalProductVariation::where('wordpress_id', $buy_together)->first();
//                        if ($variation) {
//                            $product = Product::where('wordpress_id', $variation->product_id)->first();
//                            if ($product) {
//                                $data = ProductArrivalResource::make($product)
//                                    ->additional(['variation_id' => $this->variation_id]);
//                            }
//                        }
//                    } else {
//                        $product = Product::where('wordpress_id', $buy_together)->first();
//                        $data = ProductArrivalResource::make($product);
//                    }
//                }
//            }
//        } else {
//            $product = Product::where('wordpress_id', $this->product_id)->first();
//            if (isset($this->variation_id)) {
//                $variation = DigitalProductVariation::where('wordpress_id', $this->variation_id)->first();
//                if (isset($variation)) {
//                    $data = ProductArrivalResource::make($product)
//                        ->additional(['variation_id' => $this->variation_id]);
//                } else {
//                    return null;
//                }
//            } else {
//                $data = ProductArrivalResource::make($product);
//            }
//        }
//
//        return $data; // ✅ return directly, not inside [ ]
//    }
//}