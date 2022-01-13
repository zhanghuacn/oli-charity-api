<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use HasFactory;
    use HasFactory;
    use HasExtendsProperty;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    protected $fillable = [
        'charity_id',
        'activity_id',
        'ticket_id',
        'code',
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
        'created_at' => 'datetime:Y-m-d H:i:s',
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

    protected static function booted()
    {
        static::saving(
            function (Transfer $transfer) {
                $transfer->code = $transfer->code ?? app('Kra8\Snowflake\Snowflake')->next();
            }
        );
    }
}
