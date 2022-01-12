<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\HasSettingsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Activity extends Model
{
    use Filterable;
    use SoftDeletes;
    use HasFactory;
    use HasImagesProperty;
    use HasSettingsProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriteable;
    use ModelFilter;
    use Searchable;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_REVIEW = 'REVIEW';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    // 默认缓存信息
    public const DEFAULT_SETTINGS = [
        'seat_config' => [],
    ];

    public const DEFAULT_IMAGES = [];

    public const DEFAULT_EXTENDS = [
        'specialty' => [],
        'timeline' => [],
        'participates' => 0,
        'total_income' => 0,
    ];

    public const DEFAULT_CACHE = [];

    protected $fillable = [
        'charity_id',
        'name',
        'description',
        'content',
        'location',
        'begin_time',
        'end_time',
        'price',
        'stocks',
        'is_visible',
        'is_private',
        'images',
        'settings',
        'settings->seat_config',
        'extends',
        'extends->specialty',
        'extends->timeline',
        'extends->participates',
        'extends->total_income',
        'cache',
        'status',
        'remark',
    ];

    protected $hidden = [
        'charity_id',
        'cache',
        'settings',
        'extends',
        'is_visible',
        'deleted_at',
    ];

    protected $casts = [
        'images' => 'array',
        'setting' => 'array',
        'cache' => 'array',
        'extends' => 'array',
        'is_visible' => 'bool',
        'is_private' => 'bool',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    protected $appends = [
        'state',
    ];

    public function getStateAttribute(): ?string
    {
        $state = null;
        $hours = now()->diffInHours($this->begin_time);
        if ($this->begin_time > now() && $hours > 24) {
            $state = 'POST';
        }
        if ($hours <= 24 && $hours >= 4) {
            $state = 'WAIT';
        }
        if ($hours <= 4 && $hours >= 0) {
            $state = 'CHECK';
        }
        if ($this->begin_time <= now() && $this->end_time >= now()) {
            $state = 'PROGRESS';
        }

        if ($this->end_time < now()) {
            $state = 'PAST';
        }
        return $state;
    }

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function applies(): HasMany
    {
        return $this->hasMany(Apply::class);
    }

    public function lotteries(): HasMany
    {
        return $this->hasMany(Lottery::class);
    }

    public function goods(): HasMany
    {
        return $this->hasMany(Goods::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class);
    }

    public function ticket(): Model|null
    {
        return $this->tickets()->where(['user_id' => Auth::id()])->first();
    }

    public function scopeTicket($query)
    {
//        return $query->where('votes', '>', 100);
    }

    protected static function booted()
    {
        static::saving(
            function (Activity $activity) {
            }
        );
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_visible == true;
    }
}
