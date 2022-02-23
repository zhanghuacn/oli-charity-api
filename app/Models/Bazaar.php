<?php

namespace App\Models;

use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bazaar extends Model
{
    use HasFactory;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    protected $fillable = [
        'charity_id',
        'activity_id',
        'order_id',
        'goods_id',
        'user_id',
        'price',
        'is_receive',
        'remark',
    ];

    protected $casts = [
        'price' => 'float',
        'is_receive' => 'bool',
    ];

    protected $hidden = [
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class);
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
