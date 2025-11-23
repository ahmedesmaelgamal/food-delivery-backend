<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Models\DigitalProductVariation;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyItTogetherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product=Product::where('wordpress_id', $this->woodmart_fbt_product_id)->first();
//        if ($product){
//            $productBuyItTogether=ProductDetailsResource::make($product);
//        }else{
//            $productVariant=DigitalProductVariation::where('wordpress_id', $this->woodmart_fbt_product_id)->first();
//            $productBuyItTogether=DigitalProductVariationResource::make($productVariant);
//        }


        return [
//            'id' => $this->wordpress_id ?? null,
//            'name' => $this->name ?? null,
////            'price_before_discount' => (float) round(
////                $this->regular_price ?:
////                    $this->unit_price ?:
////                        0,
////                2
////            ),
////            'price_after_discount' => (float) round(
////                $this->sale_price ?:
////                    $this->regular_price ?:
////                        $this->unit_price ?:
////                            0,
////                2
////            ),
//
//
//            'price_before_discount' => (float) $this->resource['regular_price'] ?? 0,
//            'price_after_discount' => (float) $this->resource['on_sale'] ? ((float) $this->resource['sale_price']?:(float) $this->resource['regular_price']): (float) $this->resource['regular_price'],
//
//            'thumbnail' => $this->thumbnail ?? null,
//            'images' => collect(json_decode($this->images ?? '[]', true))
//                ->map(fn($image) => $image['src'] ?? null)
//                ->filter()
//                ->toArray(),
//            'in_wishlist' => auth()->check() ? Wishlist::where('customer_id', auth()->id())->where('product_id', $this->wordpress_id ?? 0)->exists() : false,
//            'slug' => $this->slug ?? null,
//            'product_stock_count' => (int)($this->current_stock ?? 0),
////            'discount' => ($this->on_sale == true && !empty($this->resource['sale_price']))==true?  : null,
//            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0,
//
//            'is_taxable' => (bool)($this->is_taxable ?? false),
//            'variants' => DigitalProductVariationResource::collection($this->digitalVariation ?? []),
////            'related_ids' => json_decode($this->related_ids) ?? [],


            'id'=>$this->wordpress_id,
            'title'=>$this->title,
            'woodmart_main_products_discount'=>$this->woodmart_main_products_discount,
            'woodmart_fbt_product_id'=>$this->woodmart_fbt_product_id,
            'product'=>$productBuyItTogether,
            'woodmart_fbt_product_discount'=>$this->woodmart_fbt_product_discount,

        ];
    }

    /**
     * Return only the first image name from JSON or array.
     */
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
