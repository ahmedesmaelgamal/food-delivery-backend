<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class OrderResource extends JsonResource
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
            // 'user' => new UserResource($this->whenLoaded('user')),
            // 'is_guest' => $this->is_guest,
            // 'user_type' => $this->customer_type,
            'payment_status' =>$this->payment_status,
            'order_status' =>$this->order_status,
            'order_note' =>$this->order_note,
            // 'order_type' =>$this->order_type,
            // 'discount_amount' =>$this->discount_amount,
            // 'discount_type' =>$this->discount_type,
            // 'coupon_discount_bearer' =>$this->coupon_discount_bearer,
            // 'coupon_code' =>$this->coupon_code,
            // 'payment_method' =>$this->payment_method,
            // 'is_rated' => $this->is_rated,
            // 'rate'=>$this->review ? $this->review->rating : null,
            // 'orderDetails'=> OrderDetailsResource::collection($this->details),
            'created_at_date' => $this->created_at->format('d-m-Y'),
            'created_at_time' => $this->created_at->format('h:i A'),
            'price' => (float) $this->paid_amount,
            'shipping_cost' => (float) $this->shipping_cost,
            'products_total_price'=>(float) $this->products_total_price,
            'coupon_amount'=>(float) $this->coupon_amount,
        ];
    }
}
