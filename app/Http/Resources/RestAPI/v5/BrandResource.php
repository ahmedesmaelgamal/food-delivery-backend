<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class BrandResource extends JsonResource
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
            'name' => $this->name,
            'image' => $this->icon ? asset('storage/app/public/categories/' . $this->icon) : null,
            'image_alt_text' => $this->image_alt_text,
            'status' => $this->status? true : false,
        ];
    }
}
