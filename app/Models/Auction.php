<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    use HasFactory;
    use Filterable;
    use HasImagesProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use ModelFilter;
    use SoftDeletes;

    public const DEFAULT_IMAGES = [];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'images',
        'content',
        'price',
        'start_time',
        'end_time',
        'current_bid_price',
        'current_bid_user_id',
        'current_bid_time',
        'is_auction',
        'receiver',
        'receiver_address',
        'receiver_phone',
        'extends',
        'auctionable_type',
        'auctionable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'auctionable_type',
        'auctionable_id',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'current_bid_price' => 'float',
        'images' => 'array',
        'extends' => 'array',
    ];

    public function auctionable(): MorphTo
    {
        return $this->morphTo();
    }

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
        return $this->belongsTo(User::class, 'current_bid_user_id', 'id');
    }

    public function bidRecord(): HasMany
    {
        return $this->hasMany(AuctionBidRecord::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
