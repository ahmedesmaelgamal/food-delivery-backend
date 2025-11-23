<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{

    protected $fillable = [
        'user_id',
        'building_number',
        'flat_number',
        'city_id',
        'area_id',
        'postal_code',


        'floor_number',
        'phone_number',
        'address',
    ];
}
