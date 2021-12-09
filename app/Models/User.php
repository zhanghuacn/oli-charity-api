<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use App\Traits\Filterable;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasSettingsProperty;
use App\Traits\UsingUuidAsPrimaryKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property string id
 * @method static where(string $string, mixed $username)
 * @method static create(array $all)
 * @property int $id
 * @property string|null $status_remark 状态说明
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $display_status
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
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
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin \Eloquent
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
    use Filterable;

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

    public const STATUSES = [
        self::STATUS_INACTIVATED => '未激活',
        self::STATUS_ACTIVE => '正常',
        self::STATUS_FROZEN => '已冻结',
    ];

    // 默认缓存信息
    public const DEFAULT_CACHE = [];
    // 默认设置信息
    public const DEFAULT_SETTINGS = [];

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
        'settings',
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

    public function getDisplayStatusAttribute(): string
    {
        return self::STATUSES[$this->status ?? self::STATUS_ACTIVE];
    }

    public function filterKeyword($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        $keyword = sprintf('%%%s%%', $keyword);

        return $query->where(
            function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)->orWhere('username', 'like', $keyword);
            }
        );
    }

    #[ArrayShape(['token_type' => "string", 'token' => "string"])]
    public function createDeviceToken(?string $device = null): array
    {
        return [
            'token_type' => 'bearer',
            'token' => $this->createToken($device ?? Device::PC)->plainTextToken,
        ];
    }

    public function refreshLastActiveAt(): static
    {
        $this->updateQuietly(
            [
                'last_active_at' => \now(),
                'status' => self::STATUS_ACTIVE,
            ]
        );

        return $this;
    }

    public function refreshFirstActiveAt(): static
    {
        $this->first_active_at || $this->updateQuietly(
            [
                'first_active_at' => \now(),
                'status' => self::STATUS_ACTIVE,
            ]
        );

        return $this;
    }

    public function attributesToArray(): array
    {
        if (\auth()->check() && $this->is(auth()->user())) {
            return parent::attributesToArray();
        }

        return Arr::only(parent::attributesToArray(), self::SAFE_FIELDS);
    }

}
