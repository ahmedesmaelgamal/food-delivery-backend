<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class CartProduct extends Model
{

    protected $fillable = [
        'product_id',
        'variant_id',
        'customer_id',
        'quantity',
        'selected_buy_together_ids',
        'buy_together_id',
        'created_at',
        'updated_at'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id','id');
    }

//    public function cartShipping(): HasOne
//    {
//        return $this->hasOne(CartShipping::class,'cart_group_id','cart_group_id');
//    }
//
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id','wordpress_id')->where('status', 1);
    }
//    public function seller(): BelongsTo
//    {
//        return $this->belongsTo(Seller::class, 'seller_id');
//    }
//    public function shop(): BelongsTo
//    {
//        return $this->belongsTo(Shop::class, 'seller_id', 'seller_id');
//    }
//
//    public function allProducts(): BelongsTo
//    {
//        return $this->belongsTo(Product::class, 'product_id');
//    }


}
