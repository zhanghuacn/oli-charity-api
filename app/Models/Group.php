<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use Eloquent;
use EloquentFilter\Filterable;
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
 * App\Models\Group
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
 * @method static \Illuminate\Database\Eloquent\Builder|Group filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUserId($value)
 * @method static Builder|Group withTrashed()
 * @method static Builder|Group withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property int|null $owner_id 创建人
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Group simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereLike(string $column, string $value, string $boolean = 'and')
 */
class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
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
        return $this->belongsToMany(Ticket::class, 'group_ticket');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'owner_id');
    }
}
