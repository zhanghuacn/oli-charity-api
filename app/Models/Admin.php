<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Passport\HasApiTokens;

/**
 * App\Models\Admin
 *
 * @property string $name 名称
 * @property string $username 用户名
 * @property string $email 邮箱
 * @property string $password 密码
 * @property string|null $avatar 头像
 * @property \Illuminate\Support\Fluent $extends 扩展信息
 * @property string|null $last_ip 头像
 * @property string|null $last_active_at 最后活跃时间
 * @property string|null $frozen_at 冻结时间
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Query\Builder|Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereFrozenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastActiveAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereLastIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Admin withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 */
class Admin extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasExtendsProperty;

    public const SAFE_FIELDS = [
        'id',
        'name',
        'username',
        'avatar',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar',
        'email',
        'phone',
        'password',
        'extends',
        'last_ip',
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extends' => 'array',
    ];

    #[ArrayShape(['token_type' => "string", 'token' => "string", 'admin' => "array"])]
    public function createPlaceToken(string $name, array $scopes): array
    {
        return [
            'token_type' => 'Bearer',
            'token' => $this->createToken($name, $scopes)->accessToken,
            'admin' => [
                'id' => $this->id,
                'name' => $this->name,
                'avatar' => $this->avatar,
            ]
        ];
    }

    protected static function booted()
    {
        static::saving(
            function (Admin $admin) {
                $admin->name = $admin->name ?? $admin->username;
                if (Hash::needsRehash($admin->password)) {
                    $admin->password = bcrypt($admin->password);
                }
            }
        );
    }
}
