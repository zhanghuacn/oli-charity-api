<?php

namespace App\Models;

use App\Jobs\ProcessRegOliView;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasSettingsProperty;
use App\Traits\ModelFilter;
use Avatar;
use Cache;
use Carbon\Carbon;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Nubs\RandomNameGenerator\Alliteration;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Overtrue\LaravelFollow\Followable;
use Overtrue\LaravelLike\Traits\Liker;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\DatabaseNotification as Notification;

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
    use Liker;
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
        'sync',
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
        'sync' => 'bool',
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

    public function scopeOrderByAmount($query, $direction = 'desc')
    {
        return $query->orderBy(
            Order::selectRaw('SUM(amount) as total')
                ->whereColumn('user_id', 'users.id')
                ->where('payment_status', Order::STATUS_PAID)
                ->orderBy('total', $direction)
                ->limit(1),
            $direction
        );
    }

    protected static function booted()
    {
        static::saving(
            function (User $user) {
                $generator = new Alliteration();
                $user->name = $user->name ?? $generator->getName();
                $user->username = $user->username ?? Str::uuid();
                $user->avatar = $user->avatar ?? Avatar::create($user->name)->toBase64();
                $user->first_active_at = !is_null($user->getOriginal('first_active_at')) ? $user->first_active_at : null;
                $user->email_verified_at = $user->email ? now() : null;
                $user->avatar = $user->avatar ?? Avatar::create($user->name)->toGravatar();

                if (Hash::needsRehash($user->password)) {
                    Cache::put(sprintf('USER:%s:PASSWORD', $user->username), $user->password, Carbon::now()->addDay());
                    $user->password = bcrypt($user->password);
                }

                if ($user->isDirty('status') && $user->status === self::STATUS_FROZEN) {
                    $user->frozen_at = now();
                }
            }
        );

        static::created(function (User $user) {
            $user->createOrGetStripeCustomer();
            $user->createOliViewAccount($user);
        });
    }

    #[ArrayShape(['token_type' => "string", 'token' => "string", 'user' => "[]|array"])]
    public function createPlaceToken(string $name, array $scopes): array
    {
        return [
            'token_type' => 'Bearer',
            'token' => $this->createToken($name, $scopes)->accessToken,
        ];
    }

    public function createOliViewAccount(User $user): void
    {
        ProcessRegOliView::dispatch([
            'email' => $user->email,
            'phone' => $user->phone,
            'password' => Cache::get(sprintf('USER:%s:PASSWORD', $user->username), '888888')
        ]);
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
        return $this->is_visible && $this->status == self::STATUS_ACTIVE && empty($this->deleted_at);
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function routeNotificationForSns($notification): string
    {
        return sprintf('+%s', $this->phone);
    }

    public function info(): array
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'name' => $this->name,
            'backdrop' => $this->backdrop,
            'profile' => $this->profile,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            // 'email' => $this->email ? hide_email($this->email) : null,
            // 'phone' => $this->phone ? substr_replace($this->phone, '****', 3, 5) : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthday' => Carbon::parse($this->birthday)->toDateString(),
            'is_public_records' => $this->extends['records'],
            'is_public_portfolio' => $this->extends['portfolio'],
            'is_payment_method' => $this->hasPaymentMethod(),
            'type' => $this->charities()->exists() ? 'CHARITY' : ($this->sponsors()->exists() ? 'SPONSOR' : 'USER'),
            'type_name' => $this->charities()->exists() ? $this->charities()->first()->name : ($this->sponsors()->exists() ? $this->sponsors()->first()->name : ''),
        ];
    }

    public function getAvatarAttribute($value): string
    {
        return str_replace(config('filesystems.disks.s3.host'), config('filesystems.disks.s3.cloudfront'), $value);
    }
}
