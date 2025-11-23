<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyItTogether extends Model
{
    protected $fillable=[
        'wordpress_id',
        'is_notified',
        'title',
        'woodmart_main_products_discount',
        'woodmart_fbt_product_id',
        'woodmart_fbt_product_discount',
        'status'
    ];
}
