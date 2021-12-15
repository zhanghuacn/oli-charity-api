<?php

namespace App\Models;

use App\ModelFilters\OrderFilter;
use App\Traits\HasExtendsProperty;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Fluent;
use Kra8\Snowflake\Snowflake;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id 用户
 * @property int $charity_id 机构
 * @property string $order_sn 订单编号
 * @property string $transaction_id 交易号
 * @property string $pay_method 支付方式
 * @property string $currency 货币类型
 * @property string $pay_amount 付款金额
 * @property string $amount 实际支付金额
 * @property string $pay_fee 手续费
 * @property string $status 订单状态
 * @property string|null $pay_time 支付时间
 * @property mixed|null $receipt 线下支付凭证
 * @property string|null $remark 备注
 * @property string $sourceable_type
 * @property int $sourceable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model|Eloquent $sourceable
 * @method static Builder|Order filter(array $input = [], $filter = null)
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static \Illuminate\Database\Query\Builder|Order onlyTrashed()
 * @method static Builder|Order paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Order query()
 * @method static Builder|Order simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|Order whereAmount($value)
 * @method static Builder|Order whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order whereCharityId($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrency($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereLike(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Order whereOrderSn($value)
 * @method static Builder|Order wherePayAmount($value)
 * @method static Builder|Order wherePayFee($value)
 * @method static Builder|Order wherePayMethod($value)
 * @method static Builder|Order wherePayTime($value)
 * @method static Builder|Order whereReceipt($value)
 * @method static Builder|Order whereRemark($value)
 * @method static Builder|Order whereSourceableId($value)
 * @method static Builder|Order whereSourceableType($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereTransactionId($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Order withoutTrashed()
 * @mixin Eloquent
 * @property string $orderable_type
 * @property int $orderable_id
 * @property-read Model|Eloquent $orderable
 * @method static Builder|Order whereOrderableId($value)
 * @method static Builder|Order whereOrderableType($value)
 * @property Fluent $extends
 * @property string $type 订单:机构捐赠，活动捐赠，义卖商品
 * @property string $fee_amount 手续费
 * @property string $total_amount 实际到手金额
 * @property string $payment_no 交易号
 * @property string $payment_method 支付方式
 * @property string $payment_status 订单支付状态
 * @property string|null $payment_time 支付时间
 * @property-read \App\Models\User $user
 * @method static Builder|Order whereExtends($value)
 * @method static Builder|Order whereFeeAmount($value)
 * @method static Builder|Order wherePaymentMethod($value)
 * @method static Builder|Order wherePaymentNo($value)
 * @method static Builder|Order wherePaymentStatus($value)
 * @method static Builder|Order wherePaymentTime($value)
 * @method static Builder|Order whereTotalAmount($value)
 * @method static Builder|Order whereType($value)
 */
class Order extends Model
{
    use HasFactory;
    use Filterable;
    use HasExtendsProperty;
    use SoftDeletes;

    public const TYPE_CHARITY = 'CHARITY';
    public const TYPE_ACTIVITY = 'ACTIVITY';
    public const TYPE_BAZAAR = 'BAZAAR';

    public const ORDER_PAY_STRIPE = 'STRIPE';
    public const ORDER_PAY_BANK = 'BANK';

    public const STATUS_UNPAID = 'UNPAID';
    public const STATUS_IN_PAYMENT = 'IN_PAYMENT';
    public const STATUS_PAID = 'PAID';
    public const STATUS_FAIL = 'FAIL';
    public const STATUS_CLOSED = 'CLOSED';

    protected $fillable = [
        'order_sn',
        'user_id',
        'charity_id',
        'type',
        'currency',
        'amount',
        'fee_amount',
        'total_amount',
        'payment_no',
        'payment_method',
        'payment_status',
        'payment_time',
        'extends',
        'sourceable_type',
        'sourceable_id',
    ];

    protected $hidden = [];

    protected $casts = [
        'extends' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

//    public function activity(): BelongsTo
//    {
//        return $this->belongsTo(Activity::class, 'orderable_id', 'id');
//    }
//
//    public function charity(): BelongsTo
//    {
//        return $this->belongsTo(Charity::class, 'orderable_id', 'id');
//    }
//
//    public function goods(): BelongsTo
//    {
//        return $this->belongsTo(Goods::class, 'orderable_id', 'id');
//    }

    protected static function booted()
    {
        static::saving(
            function (Order $order) {
                $order->order_sn = app('Kra8\Snowflake\Snowflake')->next();
            }
        );
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(OrderFilter::class);
    }

}
