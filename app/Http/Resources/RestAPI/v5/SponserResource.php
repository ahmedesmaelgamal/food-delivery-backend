<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SponserResource extends JsonResource
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
            'photo' => $this->photo ? asset('storage/app/public/banners/' . $this->photo) : null    ,
            'banner_type' => $this->banner_type,
            'published' => $this->published,
            'url' => $this->url,
            'resource_type' => $this->resource_type,
            'resource_id' => $this->resource_id,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'button_title' => $this->button_title,
        ];
    }
}
