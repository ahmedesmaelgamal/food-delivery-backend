<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class ShippingZoneMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'method_id' => $this->method_id,
            'method_description' => $this->method_description,
            'min_amount' => $this->min_amount,
            'ignore_discounts_value' => $this->ignore_discounts_value,
            'enabled' => $this->enabled,
            'wordpress_id' => $this->wordpress_id,
            'is_notified' => $this->is_notified,
            'shipping_zone_id' => $this->shipping_zone_id,
        ];
    }
}
