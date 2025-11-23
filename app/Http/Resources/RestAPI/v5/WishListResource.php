<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class wishListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product' => new ProductResource($this->product ?? null),
            'first_name' => $this->f_name,
            'last_name' => $this->l_name,
            'username' => $this->name,
            'phone' => $this->phone,
            'address' => $this->street_address,
            'image' => $this->image ? asset('storage/app/public/users/' . $this->image) : null,
            'email' => $this->resource['email'] ?? null,
            'token' => $this->resource['token'] ?? null,
            'notification_count'=> 0,
        ];
    }
}
