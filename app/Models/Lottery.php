<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lottery extends Model
{
    use HasFactory;
    use HasImagesProperty;
    use HasExtendsProperty;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    public const TYPE_AUTOMATIC = 'AUTOMATIC';
    public const TYPE_MANUAL = 'MANUAL';

    public const DEFAULT_EXTENDS = [
        'standard_oli_register' => false,
    ];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'begin_time',
        'end_time',
        'standard_amount',
        'draw_time',
        'images',
        'extends',
        'extends->standard_oli_register',
        'status',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'images' => 'array',
        'extends' => 'array',
        'status' => 'boolean',
    ];

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Lottery $lottery) {
            $lottery->prizes()->delete();
        });
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
