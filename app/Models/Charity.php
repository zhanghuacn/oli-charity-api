<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use App\Traits\StripeConnectAccount;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Charity extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriteable;
    use Filterable;
    use ModelFilter;
    use Searchable;
    use StripeConnectAccount;

    public const GUARD_NAME = 'charity';
    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    public const STATUSES = [
        self::STATUS_WAIT => 'WAITING FOR REVIEW',
        self::STATUS_PASSED => 'APPROVED',
        self::STATUS_REFUSE => 'AUDIT REJECT',
    ];

    // 默认缓存信息
    public const DEFAULT_CACHE = [
        'amount' => 0,
    ];

    public const DEFAULT_EXTENDS = [
        'cards' => [],
    ];

    protected $fillable = [
        'name',
        'logo',
        'website',
        'backdrop',
        'description',
        'introduce',
        'staff_num',
        'credentials',
        'documents',
        'contact',
        'phone',
        'mobile',
        'email',
        'address',
        'stripe_account',
        'is_visible',
        'cache',
        'extends',
        'extends->cards',
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
        'is_visible' => 'bool',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function goods(): MorphMany
    {
        return $this->morphMany(Goods::class, 'goodsable');
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'team_id', 'id')
            ->where('guard_name', '=', Charity::GUARD_NAME);
    }

    public function news(): MorphOne
    {
        return $this->morphOne(News::class, 'newsable');
    }

    protected static function booted()
    {
        parent::boot();
        self::created(function (Charity $charity) {
            $charity->roles()->createMany([
                ['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_SUPER_ADMIN, 'team_id' => $charity->id],
                ['guard_name' => Charity::GUARD_NAME, 'name' => Role::ROLE_CHARITY_STAFF, 'team_id' => $charity->id],
            ]);
        });
    }

    public function getDisplayStatusAttribute(): string
    {
        return self::STATUSES[$this->status ?? self::STATUS_WAIT];
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
