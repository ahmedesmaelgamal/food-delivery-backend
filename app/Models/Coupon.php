<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $added_by
 * @property string $coupon_type
 * @property string $coupon_bearer
 * @property integer $seller_id
 * @property integer $customer_id
 * @property string $title
 * @property string $code
 * @property datetime $start_date
 * @property datetime $expire_date
 * @property float $min_purchase
 * @property float $max_discount
 * @property float $discount
 * @property string $discount_type
 * @property integer $limit
 */
class Coupon extends Model
{
    protected $fillable = [
        'title',
        'code',
        'start_date',
        'expire_date',
        'discount',
        'limit',
        'users_limit',
        'usage_count',
        'wordpress_id',
        'used_by',
        'is_free_shipping',
        'excluded_product_categories',
        'excluded_product_ids',
        'discount_type',
        'limit_usage_to_x_items',
        'usage_limit_per_user',
        'product_ids',
        'product_categories',
        'minimum_amount',
        'maximum_amount',
        'product_brands',
        'exclude_product_brands',
        'exclude_sale_items',
    ];



    public function scopeActive($query)
    {
        $shippingMethod = getWebConfig(name: 'shipping_method');
        $businessMode = getWebConfig(name: 'business_mode');

        return $query->when($businessMode == 'single', function ($query) {
            $query->where(['added_by' => 'admin']);
        })
            ->where(['status' => 1]);
    }

    public function order(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_code', 'code');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
