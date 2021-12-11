<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Oauth
 *
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $provider 第三方登录提供者
 * @property string $provider_id 第三方登录ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Fluent $extends
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Oauth whereExtends($value)
 */
class Oauth extends Model
{
    use HasFactory;
    use Filterable;
    use HasExtendsProperty;

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
