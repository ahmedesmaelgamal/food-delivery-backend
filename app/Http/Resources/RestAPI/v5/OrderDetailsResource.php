<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Enums\ViewPaths\Admin\Order;
use App\Models\Product;
use App\Models\DigitalProductVariation;
use App\Models\BuyItTogether;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class OrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
//    public function toArray(Request $request): array
//    {
//        $woodmart_fbt_bundles_ids = $this->woodmart_fbt_bundles_id;
//
//
//
//
//        $product = Product::find($this->product_id);
//
//        $buyTogether = null;
//        if ($this->buyItTogether) {
//            $buyTogetherProduct = Product::where('wordpress_id', $this->buyItTogether->woodmart_fbt_product_id)->first();
//            $buyTogetherVariationProduct=Product::where('wordpress_id', $buyTogetherProduct->wordpress_id)->first();//if the buy it together was variation
//            $buyTogether = [
//                'buy_together_id' => optional(
//                    BuyItTogether::where('woodmart_fbt_product_id', $this->buyItTogether->woodmart_fbt_product_id)->first()
//                )->wordpress_id,
//                'buy_together_product_id' => optional($buyTogetherProduct)->wordpress_id,
//                'buy_together_product_name' => @$buyTogetherProduct->name?$buyTogetherVariationProduct->name,
//                'buy_together_product_image' => optional(json_decode($buyTogetherProduct->images ??$buyTogetherVariationProduct->images?? '[]', true))[0]['src'] ?? null,
//                'buy_together_product_price' => json_decode($this->buy_together_price, true),
//            ];
//        }
//
//
//        return [
//            'id' => @$this->id,
//            'name' => @Product::where('id', $this->product_id)->first()->name ?? null,
//            'image' => @json_decode(Product::where('id', $this->product_id)->first()->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => @$this->qty ?? null,
//            'price' => @$this->price ?? null,
//            'product_id' => @Product::where('id', $this->product_id)->first()->wordpress_id,
//            'variation_id' => @$this->variation_id ?? null,
//            'buy_together'=>$buyTogether
//
////        {
////        'buy_together_id' => @$this->buyItTogether ? (BuyItTogether::where('woodmart_fbt_product_id',$this->buyItTogether->woodmart_fbt_product_id)->first()?->wordpress_id) : null,
////            'buy_together_product_id'=> @$this->buyItTogether ? @Product::where('wordpress_id',$this->buyItTogether->woodmart_fbt_product_id)->first()?->wordpress_id : null,
////            'buy_together_product_name'=> @$this->buy_together_id ? @Product::where('wordpress_id', $this->buyItTogether->woodmart_fbt_product_id)->first()?->name : null,
////            'buy_together_product_image'=> @$this->buy_together_id ? @json_decode(Product::where('wordpress_id',$this->buyItTogether->woodmart_fbt_product_id)->first()->images ?? '[]', true)[0]['src'] ?? null : null,
////            'buy_together_product_price'=>@json_decode($this->buy_together_price),
////            }
//
//        ];
//    }




//    public function toArray(Request $request): array
//    {
////        dd($this);
//        $product = @Product::where('wordpress_id',$this->product_id)->first();
//        if (!$product) {
//            $product=@Product::where('wordpress_id',@DigitalProductVariation::where('wordpress_id',$this->product_id)->first()->product_id)->first();
//        }
////        dd($product);
////        dd($product);
////        $productData['id'] = $this->id;
////        $productData['name'] = $product->name ?? null;
////        $productData['image'] = json_decode($product->images ?? '[]', true)[0]['src'] ?? null;
////        $productData['quantity'] = $this->qty ?? null;
////        $productData['price'] = $this->price ?? null;
////        $productData['product_id'] = $product->wordpress_id ?? null;
////        $productData['variation_id'] = $this->variation_id ?? null;
//
////dd($this);
//        $buyTogether = null;
////        dd($this->buy_together_id);
////        dd(json_decode($this->buy_together_ids, true));
//        if ($this->buy_together_ids!=null) {
////            $productIds = json_decode($buyTogetherBundle->woodmart_fbt_product_id, true) ?? [];
////
////            // Get products
////            $buyTogetherProducts = Product::whereIn('wordpress_id', $productIds)->get();
////
////            $buyTogetherVariationProductIds = DigitalProductVariation::whereIn('wordpress_id', $productIds)->pluck('id')->toArray();
////            $buyTogetherProducts=Product::whereIn('wordpress_id', $buyTogetherVariationProductIds)->get();
//
////            $buyTogetherBundle = BuyItTogether::where('wordpress_id', $this->buy_together_id)->first();
//
//            $productIds = @Product::whereIn('wordpress_id', json_decode($this->selected_buy_together_ids, true) ?? [])->pluck('wordpress_id')->toArray();
//
//            // Get direct products
//            $buyTogetherProducts = @Product::whereIn('wordpress_id', $productIds)->get();
//
//            // Get variations and their parent products
//            $buyTogetherVariationProductIds = @DigitalProductVariation::whereIn('wordpress_id', $productIds)
//                ->pluck('product_id')
//                ->toArray();
//            $buyTogetherVariationProducts = @Product::whereIn('wordpress_id', $buyTogetherVariationProductIds)->get();
//
//            // Merge both collections
//            $allBuyTogetherProducts = $buyTogetherProducts->merge($buyTogetherVariationProducts)->unique('wordpress_id');
////            dd($allBuyTogetherProducts);;
//
//
//
//
//
////            dd($this->buy_together_id);
////            $buyItTogether = BuyItTogether::where('wordpress_id', $this->buy_together_id)->where()->first();
//
//
//
//
////            $buyTogetherProduct=@BuyItTogether::where('woodmart_fbt_bundles_id',$this->buy_together_id)->first();
////            $buyTogetherProduct = @Product::whereIn('wordpress_id', json_decode($buyTogetherProduct->woodmart_fbt_product_id))->get();
////            $buyTogetherVariationProduct = @$buyTogetherProduct?$buyTogetherProduct:DigitalProductVariation::whereIn('wordpress_id', json_decode($buyTogetherProduct->woodmart_fbt_product_id))->get();
////            if ($buyTogetherVariationProduct->count() > 0){
////             $buyTogetherVariationProduct = $buyTogetherVariationProduct->first();
////            }
////            dd($buyTogetherVariationProduct,$buyTogetherProduct,$this->buy_together_id);
//
//
//
////            $buyTogetherVariationProduct = $buyTogetherProduct
////                ? Product::where('wordpress_id', $buyTogetherProduct->wordpress_id)->first()
////                : null;
////            dd($buyTogetherVariationProduct, $buyTogetherProduct);
////            $buyTogetherList = [];
////            if ($buyTogetherProduct){
//////                dd('if ');
////
////                $buyTogetherListLoop = $buyTogetherProduct;
////            }else{
//////                dd('else ');
////                $buyTogetherListLoop = $buyTogetherVariationProduct;
////            }
////            dd($this);
////            dd($this->buy_together_price);
////            dd(json_decode($this->buy_together_price, true));
//            $buyTogetherList = [];
//            foreach ( $allBuyTogetherProducts as $key => $buyTogetherProduct) {
//                $buyTogetherList []= [
//                    'product_id' => optional($buyTogetherProduct)->wordpress_id,
//                    'product_name' =>  @$buyTogetherProduct->name,
//                    'product_image' => json_decode(
//                            @$buyTogetherProduct->images
//                            ?? @$buyTogetherVariationProduct->images
//                            ?? '[]',
//                            true
//                        )[0]['src'] ?? null,
//                    'product_price' => json_decode($this->buy_together_price, true)[$key] ?? null,
//                ];
//            }
//
//            $buyTogether = [
//                'buy_together_id' => $this->buyItTogether
//                    ? optional(BuyItTogether::where('woodmart_fbt_product_id', $this->buyItTogether->woodmart_fbt_product_id)->first())->wordpress_id
//                    : null,
//                'buy_it_together'=>$buyTogetherList,
//            ];
//        }
//        return [
////            'id' => $this->id,
////            'name' => $product->name ?? null,
////            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
////            'quantity' => $this->qty ?? null,
////            'price' => $this->price ?? null,
////            'product_id' => $product->wordpress_id ?? null,
////            'variation_id' => $this->variation_id ?? null,
//            'id' => $this->id,
//            'name' => @$product->name ?? null,
//            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => @$this->qty ?? null,
//            'price' => @$this->price ?? null,
//            'product_id' => @$product->wordpress_id ?? null,
////            $productData['variation_id'] = $this->variation_id ?? null,
//            'buy_together' => @$buyTogether,
//        ];
//    }


//    public function toArray(Request $request): array
//    {
//        $product = @Product::where('wordpress_id', $this->product_id)->first();
//        if (!$product) {
//            $product = @Product::where('wordpress_id', @DigitalProductVariation::where('wordpress_id', $this->product_id)->first()->product_id)->first();
//        }
//
//        $buyTogether = null;
//
//        if ($this->buy_together_ids != null) {
//            // Decode the buy_together_ids string to get the array
//            $buyTogetherIds = json_decode($this->buy_together_ids, true) ?? [];
//            $selectedIds = json_decode($this->selected_buy_together_ids, true) ?? [];
//            $priceArray = json_decode($this->buy_together_price, true) ?? [];
//
//            if (!empty($selectedIds)) {
//                // First, check which IDs are variations and which are products
//                $buyTogetherVariations = @DigitalProductVariation::whereIn('wordpress_id', $selectedIds)->get();
//                $variationIds = $buyTogetherVariations->pluck('wordpress_id')->toArray();
////                dd($selectedIds,$variationIds);
//                // Get product IDs that are NOT variations
//                $productOnlyIds = array_diff($selectedIds, $variationIds);
//                $buyTogetherProducts = @Product::whereIn('wordpress_id', $productOnlyIds)->get();
//
//                $buyTogetherList = [];
//                $priceIndex = 0;
//
//                // Process standalone products (not variations)
//                foreach ($buyTogetherProducts as $buyTogetherProduct) {
//                    $buyTogetherList[] = [
//                        'product_id' => $buyTogetherProduct->wordpress_id,
//                        'product_name' => @$buyTogetherProduct->name,
//                        'product_image' => json_decode(@$buyTogetherProduct->images ?? '[]', true)[0]['src'] ?? null,
//                        'product_price' => $priceArray[$priceIndex] ?? null,
//                        'variation_id' => null,
//                    ];
//                    $priceIndex++;
//                }
////                dd($buyTogetherList);
//
//                // Process variations
//                foreach ($buyTogetherVariations as $variation) {
//                    // Get the parent product for this variation
//                    $parentProduct = @Product::where('wordpress_id', $variation->product_id)->first();
////dd($parentProduct,$variation,$variation->image);
//
//                    if ($parentProduct) {
//                        $buyTogetherList[] = [
//                            'product_id' => $parentProduct->wordpress_id,
//                            'product_name' => @$parentProduct->name ?? @$variation->name,
//                            'product_image' =>  @$variation->image ?? @$parentProduct->images,
//                            'product_price' => $priceArray[$priceIndex] ?? null,
//                            'variation_id' => $variation->wordpress_id,
//                        ];
//                    } else {
//                        // Fallback if parent product not found
//                        $buyTogetherList[] = [
//                            'product_id' => $variation->wordpress_id,
//                            'product_name' => @$variation->name,
//                            'product_image' =>  @$variation->image ?? @$parentProduct->images,
//                            'product_price' => $priceArray[$priceIndex] ?? null,
//                            'variation_id' => $variation->wordpress_id,
//                        ];
//                    }
//                    $priceIndex++;
//                }
//
//                // Find the BuyItTogether record
//                $buyItTogether = null;
//                if (!empty($productIds)) {
//                    // Try to find by checking if any of the product IDs match
//                    $buyItTogether = @BuyItTogether::whereIn('wordpress_id', $productIds)->first();
//
//                    // Alternative: if you need to search in the woodmart_fbt_product_id JSON field
//                    if (!$buyItTogether) {
//                        foreach ($productIds as $pid) {
//                            $buyItTogether = @BuyItTogether::where('woodmart_fbt_product_id', 'like', '%"' . $pid . '"%')->first();
//                            if ($buyItTogether) break;
//                        }
//                    }
//                }
//
//                $buyTogether = [
//                    'buy_together_id' => $buyItTogether ? $buyItTogether->wordpress_id : null,
//                    'buy_it_together' => $buyTogetherList,
//                ];
//            }
//        }
//
//        return [
//            'id' => $this->id,
//            'name' => @$product->name ?? null,
//            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => @$this->qty ?? null,
//            'price' => @$this->price ?? null,
//            'product_id' => @$product->wordpress_id ?? null,
//            'buy_together' => @$buyTogether,
//        ];
//    }


    public function toArray(Request $request): array
    {
        // Get language from additional parameters
        $lang = $this->additional['lang'] ?? 'en';

        $product = @Product::where('wordpress_id', $this->product_id)->first();
        if (!$product) {
            $product = @Product::where('wordpress_id', @DigitalProductVariation::where('wordpress_id', $this->product_id)->first()->product_id)->first();
        }

        // Handle language translation for the main product
        if ($product && $product->lang != $lang) {
            $translationField = "translation_{$lang}";
            $translationId = $product->$translationField ?? null;

            if ($translationId) {
                $translatedProduct = Product::where('wordpress_id', $translationId)
                    ->where('lang', $lang)
                    ->first();
                if ($translatedProduct) {
                    $product = $translatedProduct;
                }
            }
        }

        $buyTogether = null;

        if ($this->buy_together_ids != null) {
            // Decode the buy_together_ids string to get the array
            $buyTogetherIds = json_decode($this->buy_together_ids, true) ?? [];
            $selectedIds = json_decode($this->selected_buy_together_ids, true) ?? [];
            $priceArray = json_decode($this->buy_together_price, true) ?? [];

            if (!empty($selectedIds)) {
                // Convert selectedIds to integers for proper comparison
                $selectedIds = array_map('intval', $selectedIds);

                // First, check which IDs are variations and which are products
                $buyTogetherVariations = @DigitalProductVariation::whereIn('wordpress_id', $selectedIds)->get();
                $variationIds = $buyTogetherVariations->pluck('wordpress_id')->toArray();

                // Get product IDs that are NOT variations
                $productOnlyIds = array_diff($selectedIds, $variationIds);
//                dd($productOnlyIds,$selectedIds,$variationIds);
//                dd($productOnlyIds);
                $buyTogetherList = [];
                $priceIndex = 0;

                // Process standalone products (not variations) - if there are any
                if (!empty($productOnlyIds)) {
                    // Get products in the requested language ONLY
                    $buyTogetherProducts = @Product::whereIn('wordpress_id', $productOnlyIds)
                        ->where('lang', $lang)
                        ->get();

                    foreach ($buyTogetherProducts as $buyTogetherProduct) {
                        $buyTogetherList[] = [
                            'product_id' => $buyTogetherProduct->wordpress_id,
                            'product_name' => @$buyTogetherProduct->name,
                            'product_image' => json_decode(@$buyTogetherProduct->images ?? '[]', true)[0]['src'] ?? null,
                            'product_price' => $priceArray[$priceIndex] ?? null,
                            'variation_id' => null,
                        ];
                        $priceIndex++;
                    }

                }
                // Process variations
                // Process variations
                foreach ($buyTogetherVariations as $variation) {
                    $parentProduct = null;
                    if ($lang== Product::where('wordpress_id', $variation->product_id)->first()->lang)
                    {
                        $parentProduct=Product::where('wordpress_id', $variation->product_id)->first();
                    }else{
                        if ($lang=='en'){
                            $parentProduct=Product::where('wordpress_id', Product::where('wordpress_id', $variation->product_id)->first()->translation_en)->first();
                        }else{
                            $parentProduct=Product::where('wordpress_id', Product::where('wordpress_id', $variation->product_id)->first()->translation_ar)->first();
                        }
                    }
                    // Get the parent product for this variation in the requested language
//                    $parentProduct = @Product::where(function($query) use ($variation, $lang) {
//                        // Direct match for products in the requested language
//                        $query->where('wordpress_id', $variation->product_id)
//                            ->where('lang', $lang);
//                    })->orWhere(function($query) use ($variation, $lang) {
//                        // Or get translations where the original product is in the list
//                        $translationField = "translation_{$lang}";
//                        $query->where($translationField, $variation->product_id);
////                            ->where('lang', $lang);
//                    })->first();
//                    dd($variation->product_id,$parentProduct);

                    if ($parentProduct) {
                        $buyTogetherList[] = [
                            'product_id' => $parentProduct->wordpress_id,
                            'product_name' => @$parentProduct->name ?? @$variation->name,
                            'product_image' => @$variation->image ?? (json_decode(@$parentProduct->images ?? '[]', true)[0]['src'] ?? null),
                            'product_price' => $priceArray[$priceIndex] ?? null,
                            'variation_id' => $variation->wordpress_id,
                        ];
                    } else {
                        // Fallback if parent product not found
                        $buyTogetherList[] = [
                            'product_id' => $variation->wordpress_id,
                            'product_name' => @$variation->name,
                            'product_image' => @$variation->image,
                            'product_price' => $priceArray[$priceIndex] ?? null,
                            'variation_id' => $variation->wordpress_id,
                        ];
                    }
                    $priceIndex++;
                }

                // Find the BuyItTogether record
                $buyItTogether = null;
                if (!empty($buyTogetherIds)) {
                    // Convert buyTogetherIds to integers for consistency
                    $buyTogetherIds = array_map('intval', $buyTogetherIds);
                    $buyItTogether = @BuyItTogether::whereIn('wordpress_id', $buyTogetherIds)->first();

                    if (!$buyItTogether) {
                        foreach ($buyTogetherIds as $pid) {
                            $buyItTogether = @BuyItTogether::where('woodmart_fbt_product_id', 'like', '%"' . $pid . '"%')->first();
                            if ($buyItTogether) break;
                        }
                    }
                }

                $buyTogether = [
                    'buy_together_id' => $buyItTogether ? $buyItTogether->wordpress_id : null,
                    'buy_it_together' => $buyTogetherList,
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => @$product->name ?? null,
            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
            'quantity' => @$this->qty ?? null,
            'price' => @$this->price ?? null,
            'product_id' => @$product->wordpress_id ?? null,
            'buy_together' => @$buyTogether,
        ];
    }







//    public function toArray(Request $request): array
//    {
//        $product = @Product::where('wordpress_id', $this->product_id)->first();
//        if (!$product) {
//            $product = @Product::where('wordpress_id', @DigitalProductVariation::where('wordpress_id', $this->product_id)->first()->product_id)->first();
//        }
//
//        $buyTogether = null;
//
//        if ($this->buy_together_id != null && !empty($this->selected_buy_together_ids)) {
//            // Decode selected buy together IDs
//            $selectedIds = json_decode($this->selected_buy_together_ids, true) ?? [];
//
//            if (!empty($selectedIds)) {
//                // Get direct products that match the selected IDs
//                $directProductIds = @Product::whereIn('wordpress_id', $selectedIds)
//                    ->pluck('wordpress_id')
//                    ->toArray();
//                $buyTogetherProducts = @Product::whereIn('wordpress_id', $directProductIds)->get();
//
//                // Get variations and their parent products
//                $variationIds = array_diff($selectedIds, $directProductIds);
//                $buyTogetherVariations = @DigitalProductVariation::whereIn('wordpress_id', $variationIds)->get();
//
//                $parentProductIds = $buyTogetherVariations->pluck('product_id')->toArray();
//                $buyTogetherVariationProducts = @Product::whereIn('wordpress_id', $parentProductIds)->get();
//
//                // Merge both collections
//                $allBuyTogetherProducts = $buyTogetherProducts->merge($buyTogetherVariationProducts)->unique('wordpress_id');
//
//                // Decode buy together prices
//                $buyTogetherPrices = json_decode($this->buy_together_price, true) ?? [];
//
//                $buyTogetherList = [];
//
//                // Build the list matching selected IDs order
//                foreach ($selectedIds as $key => $selectedId) {
//                    // Check if it's a direct product
//                    $buyTogetherProduct = $buyTogetherProducts->firstWhere('wordpress_id', $selectedId);
//
//                    // If not found, check if it's a variation
//                    if (!$buyTogetherProduct) {
//                        $variation = $buyTogetherVariations->firstWhere('wordpress_id', $selectedId);
//                        if ($variation) {
//                            $buyTogetherProduct = $buyTogetherVariationProducts->firstWhere('wordpress_id', $variation->product_id);
//                            $isVariation = true;
//                            $variationId = $selectedId;
//                        }
//                    } else {
//                        $isVariation = false;
//                        $variationId = null;
//                    }
//
//                    if ($buyTogetherProduct) {
//                        $productData = [
//                            'product_id' => @$buyTogetherProduct->wordpress_id,
//                            'product_name' => @$buyTogetherProduct->name,
//                            'product_image' => json_decode(@$buyTogetherProduct->images ?? '[]', true)[0]['src'] ?? null,
//                            'product_price' => $buyTogetherPrices[$key] ?? null,
//                        ];
//
//                        // Add variation_id if it's a variation
//                        if ($isVariation) {
//                            $productData['variation_id'] = $variationId;
//                        }
//
//                        $buyTogetherList[] = $productData;
//                    }
//                }
//
//                $buyTogether = [
//                    'buy_together_id' => @$this->buy_together_id,
//                    'buy_it_together' => $buyTogetherList,
//                ];
//            }
//        }
//
//        return [
//            'id' => $this->id,
//            'name' => @$product->name ?? null,
//            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => @$this->qty ?? null,
//            'price' => @$this->price ?? null,
//            'product_id' => @$product->wordpress_id ?? null,
//            'variation_id' => @$this->variation_id ?? null,
//            'buy_together' => @$buyTogether,
//        ];
//    }




    private function getFirstImage($images): ?string
    {
        if (is_string($images)) {
            $decoded = json_decode($images, true);
        } elseif (is_array($images)) {
            $decoded = $images;
        } else {
            return null;
        }

        if (!is_array($decoded) || empty($decoded)) {
            return null;
        }

        $imageName = $decoded[0]['image_name'] ?? null;

        return $imageName ? asset('storage/product/' . $imageName) : null;
    }
}
