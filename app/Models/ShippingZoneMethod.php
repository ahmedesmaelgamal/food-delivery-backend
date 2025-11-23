<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZoneMethod extends Model
{

    protected $fillable = [
        'method_id',
        'method_description',
        'min_amount',
        'ignore_discounts_value',
        'settings',
        'shipping_zone_id',
        'enabled',
        'wordpress_id',
        'is_notified',
    ];
}
