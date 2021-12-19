<?php

namespace App\Models;

use App\ModelFilters\TicketFilter;
use App\Traits\HasExtendsProperty;
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
 * @property string $lottery_code 彩票编号
 * @property int $charity_id
 * @property int $activity_id
 * @property int $user_id
 * @property string $type 门票类型: 普通 工作人员 赞助商
 * @property string $price 门票价格
 * @property string $amount 捐款总额
 * @property int $anonymous 是否匿名捐款
 * @property Carbon $verified_at 核销时间
 * @property Fluent $extends 扩展信息
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Activity $activity
 * @property-read Charity $charity
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereAnonymous($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLotteryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereVerifiedAt($value)
 * @mixin Eloquent
 * @property int|null $team_id
 * @property string|null $table_num
 * @method static Builder|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTableNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTeamId($value)
 * @method static Builder|Ticket withTrashed()
 * @method static Builder|Ticket withoutTrashed()
 * @property-read Team|null $team
 * @property int|null $current_team_id 当前团队
 * @property-read \App\Models\Team $currentTeam
 * @property-read \Illuminate\Database\Eloquent\Collection|Ticket[] $teams
 * @property-read int|null $teams_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCurrentTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLike(string $column, string $value, string $boolean = 'and')
 */
class Ticket extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use HasExtendsProperty;

    public const TYPE_DONOR = 'DONOR';
    public const TYPE_STAFF = 'STAFF';
    public const TYPE_SPONSOR = 'SPONSOR';

    public const ROLE_HOST = 'HOST';
    public const ROLE_WORKER = 'WORKER';

    public const DEFAULT_EXTENDS = [
        'role' => '',
    ];

    protected $fillable = [
        'code',
        'lottery_code',
        'charity_id',
        'activity_id',
        'user_id',
        'current_team_id',
        'type',
        'price',
        'amount',
        'anonymous',
        'verified_at',
        'extends',
    ];

    protected $casts = [
        'extends' => 'array',
        'verified_at' => 'datetime',
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

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id', 'id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_ticket');
    }

    public function attachTeam($team, $pivotData = []): static
    {
        $team = $this->retrieveTeamId($team);
        if (is_null($this->current_team_id)) {
            $this->current_team_id = $team;
            $this->save();

            if ($this->relationLoaded('currentTeam')) {
                $this->load('currentTeam');
            }
        }
        $this->load('teams');
        if (!$this->teams->contains($team)) {
            $this->teams()->attach($team, $pivotData);
            if ($this->relationLoaded('teams')) {
                $this->load('teams');
            }
        }
        return $this;
    }

    public function detachTeam($team): static
    {
        $team = $this->retrieveTeamId($team);
        $this->teams()->detach($team);

        if ($this->relationLoaded('teams')) {
            $this->load('teams');
        }

        if ($this->teams()->count() === 0 || $this->current_team_id === $team) {
            $this->current_team_id = null;
            $this->save();

            if ($this->relationLoaded('currentTeam')) {
                $this->load('currentTeam');
            }
        }
        return $this;
    }


    protected function retrieveTeamId($team)
    {
        if (is_object($team)) {
            $team = $team->getKey();
        }
        if (is_array($team) && isset($team['id'])) {
            $team = $team['id'];
        }

        return $team;
    }

    protected static function booted()
    {
        static::saving(
            function (Ticket $ticket) {
                $ticket->code = $ticket->code ?? Str::uuid();
            }
        );
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(TicketFilter::class);
    }
}
