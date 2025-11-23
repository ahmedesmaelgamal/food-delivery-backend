<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class TagResource extends JsonResource
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
        ];
    }
}
