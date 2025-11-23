<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Wishlist;
class ProductResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  int  $orderQuantity
     * @return void
     */
    public function __construct($resource, int $orderQuantity = 1)
    {
        parent::__construct($resource);
        $this->resource['orderQuantity'] = $orderQuantity;
    }
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->wordpress_id,
            'name'                   => $this->name,
            'category_ids'            => json_decode($this->category_ids),
//            'price_before_discount' => (float) round(
//                $this->regular_price ?:
//                    $this->unit_price ?:
//                        0,
//                2
//            ),            'price_after_discount' => (float) round(
//                $this->sale_price ?:
//                    $this->regular_price ?:
//                        $this->unit_price ?:
//                            0,
//                2
//            ),
//            'price_before_discount' => (float) $this->resource['regular_price'] ?? 0,
//            'price_after_discount' => (float) $this->resource['on_sale'] ? ((float) $this->resource['sale_price']?:(float) $this->resource['regular_price']): (float) $this->resource['regular_price'],
//            'buy_it_together'=>$this->woodmart_fbt_product_id ? BuyItTogetherResource::collection(\App\Models\BuyItTogether::where('woodmart_fbt_product_id',$this->woodmart_fbt_product_id)->get()):[],
//            'thumbnail' => $this->thumbnail,
//            'images' => collect(json_decode($this->images, true))
//                ->map(fn($image) => $image['src'])
//                ->toArray(),
//            'in_wishlist'            => auth()->check() ? Wishlist::where('customer_id', auth()->id())->where('product_id', $this->wordpress_id)->exists() : false,
//            'slug'                   => $this->slug,
//            'product_stock_count'    => (int) $this->current_stock,
////            'discount' => ($this->on_sale == true && !empty($this->resource['sale_price']))==true?1 : 0,
//            'discount' => $this->resource['on_sale'] && !empty($this->resource['sale_price']) ? round((((float)$this->resource['regular_price'] - (float)$this->resource['sale_price']) / (float)$this->resource['regular_price']) * 100) : 0,
//
//            'order_quantity'         => (int) $this->orderQuantity,
//            'is_taxable'             => (bool) $this->is_taxable,
//            'variants'=> DigitalProductVariationResource::collection($this->digitalVariation),
//            'related_ids' => json_decode($this->related_ids) ?? [],

//            'related_ids' => productArrivalResource::collection(
//                \App\Models\Product::whereIn('wordpress_id',
//                    is_string($this->related_ids) ?
//                        json_decode($this->related_ids, true) ?? [] :
//                        ($this->related_ids ?? [])
//                )->whereNotNull('wordpress_id')
//                    ->get()
//            ),
        ];
    }

    private function getFirstImageUrl($images): ?string
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
