<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionBidRecord extends Model
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
        'auction_id',
        'price',
        'bid_price',
        'user_id',
        'extends',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'auctionable_type',
        'auctionable_id',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'bid_price' => 'float',
        'extends' => 'array',
    ];

    public function Auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class)->withDefault();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }
}
