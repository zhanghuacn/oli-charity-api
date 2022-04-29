<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelLike\Traits\Likeable;

class Gift extends Model
{
    use HasFactory;
    use Filterable;
    use HasImagesProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use ModelFilter;
    use SoftDeletes;
    use Likeable;

    public const DEFAULT_IMAGES = [];
    public const DEFAULT_EXTENDS = [
        'sale_num' => 0,
        'sale_income' => 0,
    ];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'content',
        'images',
        'extends',
        'giftable_type',
        'giftable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'giftable_type',
        'giftable_id',
        'deleted_at',
    ];

    protected $casts = [
        'images' => 'array',
        'extends' => 'array',
    ];

    public function giftable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getImagesAttribute($value): array
    {
        return collect($value)->transform(function ($item) {
            return str_replace(config('filesystems.disks.s3.host'), config('filesystems.disks.s3.cloudfront'), $item);
        })->toArray();
    }
}
