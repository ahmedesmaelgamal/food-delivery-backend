<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZoneLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'type',
        'wordpress_id',
        'is_notified',
        'shipping_zone_id',
        'wordpress_id'
    ];
}
