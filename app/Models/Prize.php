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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Fluent;
use function array_replace_recursive;
use function constant;
use function defined;
use function json_decode;

class Prize extends Model
{
    use HasFactory;
    use HasImagesProperty;
    use HasExtendsProperty;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    public const STATUS_ENABLE = 'ENABLE';
    public const STATUS_DISABLE = 'DISABLE';

    public const DEFAULT_WINNERS = [];
    public const DEFAULT_IMAGES = [];
    public const DEFAULT_EXTENDS = [];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'lottery_id',
        'name',
        'images',
        'description',
        'num',
        'price',
        'winners',
        'draw_time',
        'status',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'lottery_id',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'images' => 'array',
        'winners' => 'array',
        'extends' => 'array',
        'draw_time' => 'datetime:Y-m-d H:i:s',
    ];

    public function prizeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function setWinnersAttribute(array $winners)
    {
        $this->attributes['winners'] = json_encode($winners);
    }

    public function getWinnersAttribute(): Fluent
    {
        return new Fluent($this->getWinners());
    }

    public function getWinners(): array
    {
        return array_replace_recursive(defined('static::DEFAULT_WINNERS') ?
            constant('static::DEFAULT_WINNERS') : [], json_decode($this->attributes['winners'] ?? '{}', true) ?? []);
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
