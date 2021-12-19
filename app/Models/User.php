<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Traits\Filterable;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasSettingsProperty;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Overtrue\LaravelFollow\Followable;

/**
 * App\Models\User
 *
 * @property string $name
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $username
 * @property string $avatar
 * @property string $profile
 * @property string $email
 * @property string $phone
 * @property string $gender
 * @property string $status
 * @property \Carbon\Carbon $birthday
 * @property string $password
 * @property object $cache
 * @property object $extends
 * @property object $settings
 * @property bool $is_visible
 * @property \Carbon\Carbon $email_verified_at
 * @property \Carbon\Carbon $first_active_at
 * @property \Carbon\Carbon $last_active_at
 * @property \Carbon\Carbon $frozen_at
 * @method static where(string $string, mixed $username)
 * @method static create(array $all)
 * @property int $id
 * @property string|null $status_remark 状态说明
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $display_status
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstActiveAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFrozenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastActiveAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatusRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|\App\Models\UserSocialite[] $socialites
 * @property-read int|null $socialites_count
 * @property-read Collection|\App\Models\UserSocialite[] $userSocialites
 * @property-read int|null $user_socialites_count
 * @property-read Collection|\Overtrue\LaravelSubscribe\Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read Collection|User[] $followers
 * @property-read int|null $followers_count
 * @property-read Collection|User[] $followings
 * @property-read int|null $followings_count
 * @property-read Collection|\App\Models\Oauth[] $oauths
 * @property-read int|null $oauths_count
 * @method static \Illuminate\Database\Eloquent\Builder|User orderByFollowersCount(string $direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder|User orderByFollowersCountAsc()
 * @method static \Illuminate\Database\Eloquent\Builder|User orderByFollowersCountDesc()
 * @property string|null $backdrop 背景图
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBackdrop($value)
 * @property string|null $stripe_id
 * @property string|null $pm_type
 * @property string|null $pm_last_four
 * @property string|null $trial_ends_at
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePmLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTrialEndsAt($value)
 * @property-read Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasSettingsProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriter;
    use Followable;
    use Billable;

    public const GENDER_UNKNOWN = 'UNKNOWN';
    public const GENDER_MALE = 'MALE';
    public const GENDER_FEMALE = 'FEMALE';
    public const SAFE_FIELDS = [
        'id',
        'name',
        'username',
        'avatar',
        'profile',
    ];
    public const DEFAULT_AVATAR = '/img/default-avatar.png';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_INACTIVATED = 'INACTIVATED';
    public const STATUS_FROZEN = 'FROZEN';

    // 默认缓存信息
    public const DEFAULT_CACHE = [];
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
        'email',
        'phone',
        'gender',
        'status',
        'birthday',
        'email_verified_at',
        'password',
        'cache',
        'extends',
        'settings->portfolio',
        'settings->records',
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
        'email_verified_at' => 'datetime',
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

    public function oauths(): HasMany
    {
        return $this->hasMany(Oauth::class);
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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }

    public function getAvatarAttribute(): string
    {
        return $this->attributes['avatar'] ?? self::DEFAULT_AVATAR;
    }

    #[ArrayShape(['token_type' => "string", 'token' => "string", 'user' => "array"])]
    public function createDeviceToken($name, $role): array
    {
        return [
            'token_type' => 'Bearer',
            'token' => $this->createToken($name, $role)->plainTextToken,
            'user' => [
                'id' => $this->id,
                'birthday' => $this->birthday,
                'gender' => $this->gender,
                'last_name' => $this->last_name,
                'middle_name' => $this->middle_name,
                'first_name' => $this->first_name,
                'profile' => $this->profile,
                'name' => $this->name,
                'avatar' => $this->avatar,
                'is_public_records' => $this->extends['records'],
                'is_public_portfolio' => $this->extends['portfolio'],
                'backdrop' => $this->backdrop,
            ]
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
}
