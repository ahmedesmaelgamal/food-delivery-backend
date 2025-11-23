<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class YourClass
 *
 * @property int $id
 * @property string $name
 * @property string $code
 *
 * @package App\Models
 */
class DeviceToken extends Model
{
    protected $table = 'device_tokens';

    protected $fillable = [
        'device_token',
        'user_id',
    ];

    protected $casts = [

    ];
}
