<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Class YourModel
 *
 * @property int $id Primary
 * @property string $photo
 * @property string $banner_type
 * @property string $theme
 * @property int $published
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $url
 * @property string $resource_type
 * @property int $resource_id
 * @property string $title
 * @property string $sub_title
 * @property string $button_text
 * @property string $background_color
 *
 * @package App\Models
 */
class Partner extends Model
{
    use StorageTrait;

    protected $casts = [
    ];

    protected $fillable = [
//        'name',
        'image',
    ];

}
