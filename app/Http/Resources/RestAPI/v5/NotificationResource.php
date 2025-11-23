<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mpdf\Tag\Sub;

class NotificationResource extends JsonResource
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
            'title' =>$this->title,
            'description' =>$this->description,
            'reference_type' =>$this->refrence_type,
            'refrence_id' =>$this->refrence_id,
            'is_seen' => $this->is_seen
        ];
    }
}
