<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ReviewResource extends JsonResource
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
            'user' => UserResource::make($this->whenLoaded('customer')),
            'order'=>OrderResource::make($this->whenLoaded('order')),
            'rating' => $this->rating,
            'status' => $this->status,
            'is_saved' => $this->is_saved,
            'comment'=> $this->comment,
            'attachment' => $this->attachment ? asset('storage/app/public/reviews/' . $this->attachment) : null,
            'comment' => $this->comment,
            'attachment' => $this->attachment ? asset('storage/app/public/reviews/' .   $this->attachment) : null,


        ];
    }
}
