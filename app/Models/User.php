<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasSettingsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Overtrue\LaravelFollow\Followable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasSettingsProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Notifiable;
    use Favoriter;
    use Followable;
    use Billable;
    use Searchable;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    public const GENDER_UNKNOWN = 'UNKNOWN';
    public const GENDER_MALE = 'MALE';
    public const GENDER_FEMALE = 'FEMALE';

    public const SOCIALITE_GOOGLE = 'google';
    public const SOCIALITE_FACEBOOK = 'facebook';
    public const SOCIALITE_TWITTER = 'twitter';
    public const SOCIALITE_APPLE = 'apple';

    public const SAFE_FIELDS = [
        'id',
        'name',
        'username',
        'avatar',
        'profile',
    ];
    public const DEFAULT_AVATAR = '';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVATED = 'INACTIVATED';
    public const STATUS_FROZEN = 'FROZEN';

    public const SETTING_PORTFOLIO = 'portfolio';
    public const SETTING_RECORDS = 'records';

    // 默认缓存信息
    public const DEFAULT_CACHE = [];
    public const DEFAULT_EXTENDS = [
        self::SOCIALITE_GOOGLE => '',
        self::SOCIALITE_FACEBOOK => '',
        self::SOCIALITE_TWITTER => '',
        self::SOCIALITE_APPLE => '',
    ];
    // 默认设置信息
    public const DEFAULT_SETTINGS = [
        'portfolio' => true,
        'records' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'last_name',
        'username',
        'avatar',
        'profile',
        'backdrop',
        'email',
        'phone',
        'gender',
        'status',
        'birthday',
        'email_verified_at',
        'password',
        'cache',
        'extends',
        'settings->' . self::SETTING_PORTFOLIO,
        'settings->' . self::SETTING_RECORDS,
        'extends->' . self::SOCIALITE_GOOGLE,
        'extends->' . self::SOCIALITE_FACEBOOK,
        'extends->' . self::SOCIALITE_TWITTER,
        'extends->' . self::SOCIALITE_APPLE,
        'is_admin',
        'is_visible',
        'first_active_at',
        'last_active_at',
        'frozen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_visible',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cache' => 'array',
        'extends' => 'array',
        'settings' => 'array',
        'is_visible' => 'bool',
        'birthday' => 'date',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'gender' => self::GENDER_UNKNOWN,
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function charities(): BelongsToMany
    {
        return $this->belongsToMany(Charity::class);
    }

    public function sponsors(): BelongsToMany
    {
        return $this->belongsToMany(Sponsor::class);
    }

    public function getTeamIdFromCharity()
    {
        return $this->charities()->value('id');
    }

    public function getTeamIdFromSponsor()
    {
        return $this->sponsors()->value('id');
    }

    protected static function booted()
    {
        static::saving(
            function (User $user) {
                $user->name = $user->name ?? $user->username;
                $user->first_active_at = !is_null($user->getOriginal('first_active_at')) ? $user->first_active_at : null;

                if (Hash::needsRehash($user->password)) {
                    $user->password = bcrypt($user->password);
                }

                if ($user->isDirty('status') && $user->status === self::STATUS_FROZEN) {
                    $user->frozen_at = now();
                }
            }
        );

        static::created(
            function (User $user) {
                $user->createOrGetStripeCustomer();
            }
        );
    }

    public function getAvatarAttribute(): string
    {
        return $this->attributes['avatar'] ?? self::DEFAULT_AVATAR;
    }

    #[ArrayShape(['token_type' => "string", 'token' => "string", 'user' => "[]|array"])]
    public function createPlaceToken(string $name, array $scopes): array
    {
        return [
            'token_type' => 'Bearer',
            'token' => $this->createToken($name, $scopes)->accessToken,
        ];
    }

    public function refreshLastActiveAt(): static
    {
        $this->updateQuietly(
            [
                'last_active_at' => now(),
                'status' => self::STATUS_ACTIVE,
            ]
        );

        return $this;
    }

    public function refreshFirstActiveAt(): static
    {
        $this->first_active_at || $this->updateQuietly(
            [
                'first_active_at' => now(),
                'status' => self::STATUS_ACTIVE,
            ]
        );

        return $this;
    }

    public function attributesToArray(): array
    {
        if (auth()->check() && $this->is(Auth::user())) {
            return parent::attributesToArray();
        }
        return Arr::only(parent::attributesToArray(), self::SAFE_FIELDS);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_visible && $this->status == self::STATUS_ACTIVE;
    }
}
