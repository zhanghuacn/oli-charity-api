<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
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
 * @property \Illuminate\Support\Carbon $verified_at 核销时间
 * @property \Illuminate\Support\Fluent $extends 扩展信息
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Charity $charity
 * @property-read \App\Models\User $user
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
 * @mixin \Eloquent
 * @property int|null $team_id
 * @property string|null $table_num
 * @method static \Illuminate\Database\Query\Builder|Ticket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTableNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTeamId($value)
 * @method static \Illuminate\Database\Query\Builder|Ticket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Ticket withoutTrashed()
 * @property-read \App\Models\Team|null $team
 */
class Ticket extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;
    use HasExtendsProperty;

    public const TYPE_DONOR = 'DONOR';
    public const TYPE_STAFF = 'STAFF';
    public const TYPE_SPONSOR = 'SPONSOR';

    protected $fillable = [
        'code',
        'lottery_code',
        'charity_id',
        'activity_id',
        'user_id',
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

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected static function booted()
    {
        static::saving(
            function (Ticket $ticket) {
                $ticket->code = $user->code ?? Str::uuid();
            }
        );
    }
}
