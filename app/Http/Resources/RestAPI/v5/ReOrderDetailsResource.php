<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Enums\ViewPaths\Admin\Order;
use App\Models\Product;
use App\Models\BuyItTogether;
use App\Models\DigitalProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;



class ReOrderDetailsResource extends JsonResource
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
//        return [
//            'id' => $this->id,
//            'name' => Product::where('id', $this->product_id)->first()->name ?? null,
//            'image' => json_decode(Product::where('id', $this->product_id)->first()->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => $this->qty ?? null,
//            'price' => Product::where('id', $this->product_id)->first()->unit_price ?? null,
//            'product_id' => Product::where('id', $this->product_id)->first()->wordpress_id,
//            'variation_id' => $this->variation_id ?? null,
//            'buy_together_id' => $this->buyItTogether ? (BuyItTogether::where('woodmart_fbt_product_id',$this->buyItTogether->woodmart_fbt_product_id)->first()?->wordpress_id) : null,
//            'buy_together_product_id'=> $this->buyItTogether ? Product::where('wordpress_id',$this->buyItTogether->woodmart_fbt_product_id)->first()?->wordpress_id : null,
//            'buy_together_product_name'=> $this->buy_together_id ? Product::where('wordpress_id', $this->buyItTogether->woodmart_fbt_product_id)->first()?->name : null,
//            'buy_together_product_image'=> $this->buy_together_id ? json_decode(Product::where('wordpress_id',$this->buyItTogether->woodmart_fbt_product_id)->first()->images ?? '[]', true)[0]['src'] ?? null : null,
//            'buy_together_product_price'=> (int) $this->buy_together_id ? Product::where('wordpress_id', $this->buyItTogether->woodmart_fbt_product_id)->first()?->unit_price : 0,
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
//        if ($this->buy_together_id!=null) {
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
//            $productIds = Product::whereIn('wordpress_id', json_decode($this->selected_buy_together_ids, true) ?? [])->pluck('wordpress_id')->toArray();
//
//            // Get direct products
//            $buyTogetherProducts = Product::whereIn('wordpress_id', $productIds)->get();
//
//            // Get variations and their parent products
//            $buyTogetherVariationProductIds = DigitalProductVariation::whereIn('wordpress_id', $productIds)
//                ->pluck('product_id')
//                ->toArray();
//            $buyTogetherVariationProducts = Product::whereIn('wordpress_id', $buyTogetherVariationProductIds)->get();
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
//                            $buyTogetherProduct->images
//                            ?? $buyTogetherVariationProduct->images
//                            ?? '[]',
//                            true
//                        )[0]['src'] ?? null,
//                    'product_price' => json_decode($this->buy_together_price, true)[$key] ?? null,
//                ];
//            }
//
//            $buyTogether = [
//                'buy_together_id' => optional(
//                    BuyItTogether::where('woodmart_fbt_product_id', $this->buyItTogether->woodmart_fbt_product_id)->first()
//                )->wordpress_id,
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
//            'quantity' => $this->qty ?? null,
//            'price' => $this->price ?? null,
//            'product_id' => $product->wordpress_id ?? null,
////            $productData['variation_id'] = $this->variation_id ?? null,
//            'buy_together' => $buyTogether,
//        ];
//    }




//    public function toArray(Request $request): array
//    {
//        $product = @Product::where('wordpress_id',$this->product_id)->first();
//        $variation = null;
//
//        if (!$product) {
//            $variation = @DigitalProductVariation::where('wordpress_id',$this->product_id)->first();
//            $product = @Product::where('wordpress_id', $variation?->product_id)->first();
//        }
//
//        // Get current price from product or variation
//        $currentPrice = $variation ?  (((float) @$variation->on_sale ? ((float) @$variation->sale_price?:(float) @$variation->regular_price): (float) @$variation->regular_price)): ((float) @$product->on_sale ? ((float) @$product->sale_price?:(float) @$product->regular_price): (float) @$product->regular_price);
////        dd($currentPrice);
//
//        $buyTogether = null;
//
//        if ($this->buy_together_id != null) {
//            $selectedIds = json_decode(@$this->selected_buy_together_ids, true) ?? [];
//
//            $buyTogetherList = [];
//            foreach ($selectedIds as $selectedId) {
//                // Try to find as a product first
//                $buyTogetherProduct = Product::where('wordpress_id', $selectedId)->first();
//                $buyTogetherVariation = null;
//                $currentBuyTogetherPrice = null;
//
//                if ($buyTogetherProduct) {
//                    // It's a direct product
//                    $currentBuyTogetherPrice = (((float) @$buyTogetherProduct->on_sale ? ((float) @$buyTogetherProduct->sale_price?:(float) @$buyTogetherProduct->regular_price): (float) @$buyTogetherProduct->regular_price));
//                } else {
//                    // Try to find as a variation
//                    $buyTogetherVariation = DigitalProductVariation::where('wordpress_id', $selectedId)->first();
//                    if ($buyTogetherVariation) {
//                        $currentBuyTogetherPrice = (((float) @$buyTogetherVariation->on_sale ? ((float) @$buyTogetherVariation->sale_price?:(float) @$buyTogetherVariation->regular_price): (float) @$buyTogetherVariation->regular_price));
//                        // Get parent product for name and image
//                        $buyTogetherProduct = Product::where('wordpress_id', $buyTogetherVariation->product_id)->first();
//                    }
//                }
//
//                if ($buyTogetherProduct || $buyTogetherVariation) {
//                    $buyTogetherList[] = [
//                        'product_id' => (int)$selectedId,
//                        'product_name' => @$buyTogetherProduct->name ?? null,
//                        'product_image' => json_decode(
//                                @$buyTogetherProduct->images
//                                ?? @$buyTogetherVariation->images
//                                ?? '[]',
//                                true
//                            )[0]['src'] ?? null,
//                        'product_price' => $currentBuyTogetherPrice ?? null,
//                    ];
//                }
//            }
//
//            $buyTogether = [
//                'buy_together_id' => optional(
//                    BuyItTogether::where('woodmart_fbt_product_id', @$this->buyItTogether->woodmart_fbt_product_id)->first()
//                )->wordpress_id,
//                'buy_it_together' => $buyTogetherList,
//            ];
//        }
//
//        return [
//            'id' => (int)$this->id,
//            'name' => @$product->name ?? null,
//            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
//            'quantity' => @$this->qty ?? null,
//            'price' => @$currentPrice ?? null,
//            'product_id' => @$product->wordpress_id ?? null,
//            'buy_together' => @$buyTogether,
//        ];
//    }


    public function toArray(Request $request): array
    {
        $product = @Product::where('wordpress_id', $this->product_id)->first();
        $variation = null;

        if (!$product) {
            $variation = @DigitalProductVariation::where('wordpress_id', $this->product_id)->first();
            $product = @Product::where('wordpress_id', $variation?->product_id)->first();
        }

        // Get current price from product or variation
        $currentPrice = $variation
            ? (((float) @$variation->on_sale ? ((float) @$variation->sale_price ?: (float) @$variation->regular_price) : (float) @$variation->regular_price))
            : ((float) @$product->on_sale ? ((float) @$product->sale_price ?: (float) @$product->regular_price) : (float) @$product->regular_price);

        $buyTogether = null;

        if ($this->buy_together_id != null) {
            // Decode the selected buy together IDs
            $selectedIds = json_decode(@$this->selected_buy_together_ids, true) ?? [];

            if (!empty($selectedIds)) {
                // First, check which IDs are variations and which are products
                $buyTogetherVariations = @DigitalProductVariation::whereIn('wordpress_id', $selectedIds)->get();
                $variationIds = $buyTogetherVariations->pluck('wordpress_id')->toArray();

                // Get product IDs that are NOT variations
                $productOnlyIds = array_diff($selectedIds, $variationIds);
                $buyTogetherProducts = @Product::whereIn('wordpress_id', $productOnlyIds)->get();

                $buyTogetherList = [];

                // Process standalone products (not variations)
                foreach ($buyTogetherProducts as $buyTogetherProduct) {
                    $currentBuyTogetherPrice = (((float) @$buyTogetherProduct->on_sale
                        ? ((float) @$buyTogetherProduct->sale_price ?: (float) @$buyTogetherProduct->regular_price)
                        : (float) @$buyTogetherProduct->regular_price));

                    $buyTogetherList[] = [
                        'product_id' => (int) $buyTogetherProduct->wordpress_id,
                        'product_name' => @$buyTogetherProduct->name,
                        'product_image' => json_decode(@$buyTogetherProduct->images ?? '[]', true)[0]['src'] ?? null,
                        'product_price' => $currentBuyTogetherPrice ?? null,
                        'variation_id' => null,
                    ];
                }

                // Process variations
                foreach ($buyTogetherVariations as $buyTogetherVariation) {
                    $currentBuyTogetherPrice = (((float) @$buyTogetherVariation->on_sale
                        ? ((float) @$buyTogetherVariation->sale_price ?: (float) @$buyTogetherVariation->regular_price)
                        : (float) @$buyTogetherVariation->regular_price));

                    // Get the parent product for this variation
                    $parentProduct = @Product::where('wordpress_id', $buyTogetherVariation->product_id)->first();

                    if ($parentProduct) {
                        $buyTogetherList[] = [
                            'product_id' => (int) $parentProduct->wordpress_id,
                            'product_name' => @$parentProduct->name ?? @$buyTogetherVariation->name,
                            'product_image' => json_decode(
                                    @$buyTogetherVariation->images ?? @$parentProduct->images ?? '[]',
                                    true
                                )[0]['src'] ?? null,
                            'product_price' => $currentBuyTogetherPrice ?? null,
                            'variation_id' => (int) $buyTogetherVariation->wordpress_id,
                        ];
                    } else {
                        // Fallback if parent product not found
                        $buyTogetherList[] = [
                            'product_id' => (int) $buyTogetherVariation->wordpress_id,
                            'product_name' => @$buyTogetherVariation->name,
                            'product_image' => json_decode(@$buyTogetherVariation->images ?? '[]', true)[0]['src'] ?? null,
                            'product_price' => $currentBuyTogetherPrice ?? null,
                            'variation_id' => (int) $buyTogetherVariation->wordpress_id,
                        ];
                    }
                }

                $buyTogether = [
                    'buy_together_id' => optional(
                        BuyItTogether::where('woodmart_fbt_product_id', @$this->buyItTogether->woodmart_fbt_product_id)->first()
                    )->wordpress_id,
                    'buy_it_together' => $buyTogetherList,
                ];
            }
        }

        return [
            'id' => (int) $this->id,
            'name' => @$product->name ?? null,
            'image' => json_decode($product->images ?? '[]', true)[0]['src'] ?? null,
            'quantity' => @$this->qty ?? null,
            'price' => @$currentPrice ?? null,
            'product_id' => @$product->wordpress_id ?? null,
            'variation_id' => $variation ? (int) $variation->wordpress_id : null,
            'buy_together' => @$buyTogether,
        ];
    }








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
