<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserSocialite
 *
 * @property int $id
 * @property int $user_id
 * @property string $provider 第三方登录提供者
 * @property string $provider_id 第三方登录ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSocialite whereUserId($value)
 * @mixin \Eloquent
 */
class UserSocialite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
