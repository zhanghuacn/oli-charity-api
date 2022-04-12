<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasExtendsProperty;
    use HasRoles;
    use Notifiable;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    protected string $guard_name = 'admin';

    public const GUARD_NAME = 'admin';

    public const SAFE_FIELDS = [
        'id',
        'name',
        'username',
        'avatar',
    ];

    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
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
        'extends',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extends' => 'array',
        'last_active_at' => 'datetime:Y-m-d H:i:s',
    ];

    #[ArrayShape(['token_type' => "string", 'token' => "string", 'admin' => "array"])]
    public function createPlaceToken(string $name, array $scopes): array
    {
        return [
            'token_type' => 'Bearer',
            'token' => $this->createToken($name, $scopes)->accessToken,
        ];
    }

    public function news()
    {
        return $this->morphOne(News::class, 'newsable');
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

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
