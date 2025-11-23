<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class DigitalProductVariationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
//    public function toArray(Request $request): array
//    {
//        $price_after_discount = $this->additional['price_after_discount'] ?? null;
//
//
//
////        dd(collect(json_decode($this->resource)),collect(json_decode($this->resource['id'])),collect(json_decode($this->resource['image'])));
////        dd(
////            $this->resource['id'],
////            $this->resource['image'],
////            $this->resource['attributes']
////        );
//        return [
////            'id'=> $this->resource['wordpress_id'] ,
////            'sku' => $this->sku,
////            'description' => $this->resource['description'] ?? '',
//////            'price' => (float) $this->price ?? 0,
//////            'regular_price' => $this->resource['regular_price'] ?? null,
//////            'sale_price' => $this->resource['sale_price'] ?? null,
////
////
////            'price_before_discount' => (float) $this->resource['regular_price'] ?? 0,
////            'price_after_discount' =>  (float) $this->resource['sale_price']?:(float) $this->resource['regular_price'],
////
////
////
//////            'regular_price' => $this->resource['regular_price'] ?? null,
//////            'sale_price' => $this->resource['sale_price'] ?? null,
////            'date_on_sale_from' => $this->resource['date_on_sale_from'] ?? null,
////            'date_on_sale_to' => $this->resource['date_on_sale_to'] ?? null,
//////            'discount' => ($this->on_sale == true && !empty($this->resource['sale_price']))==true?1 : 0,
////            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0,
////
////            'is_taxable' => (boolean) $this->resource['tax_status'] ,
////            'product_stock_count' => $this->resource['stock_status'] == 'instock'? 1 : 0,
////            'images' => collect($this->resource['image'], true)
//////                ->map(fn($image) => $image['src'])
////                ->toArray(),
////            //            'attributes' => collect(json_decode($this->resource['attributes'] ?? '[]', true))
//////                ->map(function ($item) {
//////                    return ( $item['option'] ?? '');
//////                })
//////                ->implode(', '),
////            'menu_order' => $this->resource['menu_order'] ?? 0,
//
//
//
//
//
//            'id' => $this->wordpress_id ?? null,
//            'name' => collect(json_decode($this->attributes ?? '[]', true))
//                    ->pluck('name')
//                    ->first() ?? null,
//            'category_ids' => json_decode(@Product::where('wordpress_id',$this->product_id)->first()->category_ids) ?? null,
//
//            'option' => collect(json_decode($this->attributes ?? '[]', true))
//                    ->pluck('option')
//                    ->first() ?? null,
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
//            'price_after_discount' => $price_after_discount?:((float) $this->resource['on_sale'] ? ((float) $this->resource['sale_price']?:(float) $this->resource['regular_price']): (float) $this->resource['regular_price']),
//
////            'thumbnail' => $this->thumbnail ?? null,
////            dd($this->image),
////            'images' => collect(json_decode($this->images ?? '[]', true))
////                ->map(fn($image) => $image['src'] ?? null)
////                ->filter()
////                ->toArray(),
//            'images' => is_array($this->image) ? $this->image : [$this->image],
//            'in_wishlist' => auth()->check() ? Wishlist::where('customer_id', auth()->id())->where('product_id', $this->wordpress_id ?? 0)->exists() : false,
//            'slug' => collect(json_decode($this->attributes ?? '[]', true))
//                    ->pluck('slug')
//                    ->first() ?? null,//            dd($this->current_stock),
//            'product_stock_count' => (int)($this->stock_status=='instock' ?1: 0),
////            'discount' => ($this->on_sale == true && !empty($this->resource['sale_price']))==true?  : null,
//            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0,
//
//            'is_taxable' => (bool)($this->is_taxable ?? false),
////            'variants' => DigitalProductVariationResource::collection($this->digitalVariation ?? []),
////            'related_ids' => json_decode($this->related_ids) ?? [],
//        ];
//    }




    public function toArray(Request $request): array
    {
        $lang = $this->additional['lang'] ?? 'en';
        $price_after_discount = $this->additional['price_after_discount'] ?? null;

        // Get the parent product
        $parentProduct = Product::where('wordpress_id', $this->product_id)->first();

        // Handle language translation for parent product
        if ($parentProduct && $parentProduct->lang != $lang) {
            $translationField = "translation_{$lang}";
            $translationId = $parentProduct->$translationField ?? null;

            if ($translationId) {
                $translatedParentProduct = Product::where('wordpress_id',$translationId)->first();
                if ($translatedParentProduct) {
                    $parentProduct = $translatedParentProduct;
                }
            }
        }
//        dd($parentProduct);
        return [
            'id' => $this->wordpress_id ?? null,
            'name' => collect(json_decode($this->attributes ?? '[]', true))
                    ->pluck('name')
                    ->first() ?? null,
            'category_ids' => $parentProduct ? json_decode($parentProduct->category_ids) : null,

            'option' => collect(json_decode($this->attributes ?? '[]', true))
                    ->pluck('option')
                    ->first() ?? null,

            'price_before_discount' => (float) $this->resource['regular_price'] ?? 0,
            'price_after_discount' => $price_after_discount ?: ((float) $this->resource['on_sale'] ? ((float) $this->resource['sale_price'] ?: (float) $this->resource['regular_price']) : (float) $this->resource['regular_price']),

            'images' => is_array($this->image) ? $this->image : [$this->image],
            'in_wishlist' => auth()->check() ? Wishlist::where('customer_id', auth()->id())->where('product_id', $this->wordpress_id ?? 0)->exists() : false,
            'slug' => collect(json_decode($this->attributes ?? '[]', true))
                    ->pluck('slug')
                    ->first() ?? null,
            'product_stock_count' => (int)($this->stock_status == 'instock' ? 1 : 0),
            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0,

            'is_taxable' => (bool)($this->is_taxable ?? false),
        ];
    }
}
