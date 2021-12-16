<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * App\Models\TeamInvite
 *
 * @property int $id
 * @property int $ticket_id 门票
 * @property int $user_id 用户
 * @property int $team_id 团队
 * @property string $type 类型
 * @property string $accept_token 接受token
 * @property string $deny_token 拒绝token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Team $team
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite newQuery()
 * @method static \Illuminate\Database\Query\Builder|TeamInvite onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite query()
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereAcceptToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereDenyToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereLike(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|TeamInvite withTrashed()
 * @method static \Illuminate\Database\Query\Builder|TeamInvite withoutTrashed()
 * @mixin \Eloquent
 * @property int $inviter_id 邀请人
 * @property-read \App\Models\User $inviter
 * @method static \Illuminate\Database\Eloquent\Builder|TeamInvite whereInviterId($value)
 */
class TeamInvite extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    public const TYPE_INVITE = 'INVITE';
    public const TYPE_REQUEST = 'REQUEST';

    protected $fillable = [
        'ticket_id',
        'inviter_id',
        'team_id',
        'type',
        'accept_token',
        'deny_token',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id', 'id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    protected static function booted()
    {
        static::saving(
            function (TeamInvite $teamInvite) {
                $teamInvite->accept_token = $teamInvite->accept_token ?? md5(Str::uuid());
                $teamInvite->deny_token = $teamInvite->deny_token ?? md5(Str::uuid());
            }
        );
    }
}
