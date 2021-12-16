<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Team
 *
 * @property int $id
 * @property int $charity_id 机构
 * @property int $activity_id 活动
 * @property int $user_id 创建人
 * @property string $name 名称
 * @property string $description 描述
 * @property int $num 团队人数限制
 * @property array|null $extends 扩展信息
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Activity $activity
 * @property-read Charity $charity
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Team filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static Builder|Team onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUserId($value)
 * @method static Builder|Team withTrashed()
 * @method static Builder|Team withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property int|null $owner_id 创建人
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereOwnerId($value)
 */
class Team extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasExtendsProperty;

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'num',
        'owner_id',
        'extends'
    ];

    protected $casts = [
        'extends' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'team_ticket');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'owner_id');
    }
}
