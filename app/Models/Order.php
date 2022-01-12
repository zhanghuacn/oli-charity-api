<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use Filterable;
    use HasExtendsProperty;
    use ModelFilter;
    use SoftDeletes;

    public const TYPE_CHARITY = 'CHARITY';
    public const TYPE_ACTIVITY = 'ACTIVITY';
    public const TYPE_BAZAAR = 'BAZAAR';
    public const TYPE_TICKETS = 'TICKETS';

    public const ORDER_PAY_STRIPE = 'STRIPE';
    public const ORDER_PAY_BANK = 'BANK';

    public const PAYMENT_OFFLINE = 'OFFLINE';
    public const PAYMENT_ONLINE = 'ONLINE ';

    public const STATUS_UNPAID = 'UNPAID';
    public const STATUS_IN_PAYMENT = 'IN_PAYMENT';
    public const STATUS_PAID = 'PAID';
    public const STATUS_FAIL = 'FAIL';
    public const STATUS_CLOSED = 'CLOSED';

    protected $fillable = [
        'order_sn',
        'user_id',
        'charity_id',
        'activity_id',
        'type',
        'currency',
        'amount',
        'fee_amount',
        'total_amount',
        'payment_no',
        'payment_type',
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

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted()
    {
        static::saving(
            function (Order $order) {
                $order->order_sn = $order->order_sn ?? app('Kra8\Snowflake\Snowflake')->next();
            }
        );
    }
}
