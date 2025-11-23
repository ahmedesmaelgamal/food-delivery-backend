<?php

namespace App\Http\Resources\RestAPI\v5;

use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserAddressResource extends JsonResource
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
            'building_number' => $this->building_number,
            'flat_number' => $this->flat_number,
            'city' => CityResource::make(State::find($this->city_id)),
            'area' => CityResource::make(City::find($this->area_id)),
            'postal_code' => $this->postal_code,
            'floor_number' => $this->floor_number,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
        ];
    }
}
