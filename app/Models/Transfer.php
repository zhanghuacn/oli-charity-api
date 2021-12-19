<?php

namespace App\Models;

use App\ModelFilters\TransferFilter;
use App\Traits\HasExtendsProperty;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Transfer
 *
 * @property int $id
 * @property int $charity_id 机构
 * @property int $activity_id 活动
 * @property int $ticket_id 门票
 * @property int $user_id 用户
 * @property string $amount 转账金额
 * @property string|null $status 核验时间
 * @property string|null $remark 备注
 * @property int|null $reviewer 审核人
 * @property array|null $voucher 凭证
 * @property \Illuminate\Support\Fluent $extends 扩展信息
 * @property string|null $verified_at 核验时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Charity $charity
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer newQuery()
 * @method static \Illuminate\Database\Query\Builder|Transfer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereLike(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereTicketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereVoucher($value)
 * @method static \Illuminate\Database\Query\Builder|Transfer withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Transfer withoutTrashed()
 * @mixin \Eloquent
 * @property string|null $transfer_sn
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereTransferSn($value)
 * @property string $code 转账编号
 * @method static \Illuminate\Database\Eloquent\Builder|Transfer whereCode($value)
 */
class Transfer extends Model
{
    use HasFactory;
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use HasExtendsProperty;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    protected $fillable = [
        'charity_id',
        'activity_id',
        'ticket_id',
        'user_id',
        'amount',
        'status',
        'remark',
        'reviewer',
        'voucher',
        'extends',
        'verified_at',
    ];

    protected $casts = [
        'voucher' => 'array',
        'extends' => 'array',
        'created_at' => 'datetime',
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

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(TransferFilter::class);
    }

    protected static function booted()
    {
        static::saving(
            function (Transfer $transfer) {
                $transfer->transfer_sn = $transfer->transfer_sn ?? app('Kra8\Snowflake\Snowflake')->next();
            }
        );
    }
}
