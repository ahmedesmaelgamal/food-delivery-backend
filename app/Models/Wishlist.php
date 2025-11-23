<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Wishlist
 *
 * @property int $id
 * @property int $customer_id
 * @property int $product_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Wishlist extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'product_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'customer_id' => 'integer',
        'product_id' => 'integer',
    ];

    // public static function scopeGetWishlist( $customerId = 0,$all = false, $limit = 10, $offset = 0)
    // {
    //     $query = static::where('customer_id', $customerId)
    //         ->with('wishlistProduct');
    //     if ($all) {
    //         return $query->get()->pluck('product_id')->toArray();
    //     }

    //     return $query->paginate($limit, ['*'], 'page', intval($offset / $limit) + 1)->pluck('product_id')->toArray();
    // }


    public function scopeGetWishlist($query)
    {
        return $query->where('customer_id',34)
            ->with(['wishlistProduct' => function($q) {
                $q->select('id', 'name','unit_price','images',
                'slug');
            }])
            ->get();
    }
    public function wishlistProduct()
    {
        return $this->belongsTo(Product::class,  'product_id')->active();
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->select(['id','slug']);
    }

    public function productFullInfo(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

