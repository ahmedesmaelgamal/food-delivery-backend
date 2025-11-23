<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            $this->type =>$this->value,
        ];
    }


}
