<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentViewdProduct extends Model
{
    use HasFactory;
    protected $table = 'recently_viewd';
    protected $fillable = [
        'customer_id',
        'product_id',
    ];
}
