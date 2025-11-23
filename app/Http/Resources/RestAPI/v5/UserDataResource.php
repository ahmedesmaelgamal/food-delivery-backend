<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserDataResource extends JsonResource
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
            'first_name' => $this->f_name?? null,
            'last_name' => $this->l_name?? null,
            'username' => $this->name?? null,
            'phone' => $this->phone?? null,
            'address' => $this->street_address?? null,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'email' => $this->resource['email'] ?? null,
            'token' => $this->resource['token'] ?? null,
            'notification_count'=> 0,
            'is_registered' => $this->resource['is_registered'] ?? false,
        ];
    }
}
