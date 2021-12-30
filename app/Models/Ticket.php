<?php

namespace App\Models;

use App\ModelFilters\TicketFilter;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property string $code 门票编码
 * @property string|null $lottery_code 彩票编号
 * @property int $charity_id 慈善机构
 * @property int $activity_id 活动
 * @property int $user_id 用户
 * @property int|null $group_id 当前团队
 * @property string|null $seat_num 桌号
 * @property string $type 门票类型: 普通 工作人员 赞助商
 * @property string $price 门票价格
 * @property string $amount 捐款总额
 * @property int $anonymous 是否匿名捐款
 * @property Carbon|null $verified_at 核销时间
 * @property Fluent $extends 扩展信息
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Charity $charity
 * @property-read \App\Models\Group|null $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static Builder|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAnonymous($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLike(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLotteryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTableNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereVerifiedAt($value)
 * @method static Builder|Ticket withTrashed()
 * @method static Builder|Ticket withoutTrashed()
 * @mixin Eloquent
 */
class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;
    use HasExtendsProperty;

    public const TYPE_DONOR = 'DONOR';
    public const TYPE_STAFF = 'STAFF';
    public const TYPE_HOST = 'HOST';
    public const TYPE_SPONSOR = 'SPONSOR';

    public const DEFAULT_EXTENDS = [];

    protected $fillable = [
        'code',
        'lottery_code',
        'charity_id',
        'activity_id',
        'user_id',
        'group_id',
        'type',
        'price',
        'amount',
        'anonymous',
        'verified_at',
        'extends',
    ];

    protected $casts = [
        'extends' => 'array',
        'verified_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $attributes = [
        'type' => self::TYPE_DONOR,
    ];

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_ticket');
    }

    public function attachGroup($group, $pivotData = []): static
    {
        $group = $this->retrieveGroupId($group);
        if (is_null($this->group_id)) {
            $this->group_id = $group;
            $this->save();

            if ($this->relationLoaded('group')) {
                $this->load('group');
            }
        }
        $this->load('groups');
        if (!$this->groups->contains($group)) {
            $this->groups()->attach($group, $pivotData);
            if ($this->relationLoaded('groups')) {
                $this->load('groups');
            }
        }
        return $this;
    }

    public function detachGroup($group): static
    {
        $group = $this->retrieveGroupId($group);
        $this->groups()->detach($group);

        if ($this->relationLoaded('groups')) {
            $this->load('groups');
        }

        if ($this->groups()->count() === 0 || $this->group_id === $group) {
            $this->group_id = null;
            $this->save();

            if ($this->relationLoaded('group')) {
                $this->load('group');
            }
        }
        return $this;
    }


    protected function retrieveGroupId($group)
    {
        if (is_object($group)) {
            $group = $group->getKey();
        }
        if (is_array($group) && isset($group['id'])) {
            $group = $group['id'];
        }

        return $group;
    }

    protected static function booted()
    {
        static::saving(
            function (Ticket $ticket) {
                $ticket->code = $ticket->code ?? Str::uuid();
            }
        );
    }
}
