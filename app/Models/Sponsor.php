<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelSubscribe\Traits\Subscribable;

class Sponsor extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Subscribable;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    // 默认缓存信息
    public const DEFAULT_CACHE = [];

    protected $fillable = [
        'name',
        'logo',
        'website',
        'description',
        'introduce',
        'credentials',
        'documents',
        'contact',
        'phone',
        'mobile',
        'email',
        'address',
        'cache',
        'extends',
        'status',
        'remark',
    ];

    protected $hidden = [
        'is_visible',
    ];

    protected $casts = [
        'credentials' => 'array',
        'documents' => 'array',
        'cache' => 'array',
        'extends' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    public function goods(): MorphMany
    {
        return $this->morphMany(Goods::class, 'sourceable');
    }

    protected static function booted()
    {
        static::saving(
            function (Charity $charity) {
            }
        );
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }
}
