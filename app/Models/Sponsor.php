<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Sponsor extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriteable;
    use Filterable;
    use ModelFilter;
    use Searchable;

    public const GUARD_NAME = 'sponsor';
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

    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function goods(): MorphMany
    {
        return $this->morphMany(Goods::class, 'goodsable');
    }

    public function prizes(): MorphMany
    {
        return $this->morphMany(Prize::class, 'prizeable');
    }

    protected static function booted()
    {
        self::created(function (Charity $charity) {
            $charity->roles()->createMany([
                ['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_SPONSOR_SUPER_ADMIN, 'team_id' => $charity->id],
                ['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_SPONSOR_STAFF, 'team_id' => $charity->id],
            ]);
        });
        static::saving(
            function (Sponsor $sponsor) {
            }
        );
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status == self::STATUS_PASSED;
    }
}
