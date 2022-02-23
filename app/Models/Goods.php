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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goods extends Model
{
    use HasFactory;
    use Filterable;
    use HasImagesProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use ModelFilter;
    use SoftDeletes;

    public const STATUS_ENABLE = 'ENABLE';
    public const STATUS_DISABLE = 'DISABLE';

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
        'price',
        'stock',
        'status',
        'images',
        'extends',
        'cache',
        'goodsable_type',
        'goodsable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'images' => 'array',
        'extends' => 'array',
        'cache' => 'array',
    ];

    public function goodsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
