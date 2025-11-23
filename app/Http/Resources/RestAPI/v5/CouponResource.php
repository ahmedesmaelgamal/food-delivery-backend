<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class CouponResource extends JsonResource
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
            'user' => UserResource::make($this->whenLoaded('user')),
            'coupon_type' => $this->coupon_type,
            'coupon_bearer' => $this->coupon_bearer,
            'title' =>$this->title,
            'code' =>$this->code,
            'start_date' =>$this->start_date,
            'expiry_date' =>$this->expiry_date,
            'min_purchase' =>$this->min_purchase,
            'max_discount' =>$this->max_discount,
            'discount' =>$this->discount,
            'discount_type' =>$this->discount_type,
            'status' =>$this->status,
            'limit' =>$this->limit,
        ];
    }
}
