<?php

namespace App\Models;

use App\ModelFilters\GoodsFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goods extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'description',
        'content',
        'images',
        'tag',
        'stock',
        'status',
        'sourceable_type',
        'sourceable_id',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function originable(): MorphTo
    {
        return $this->morphTo();
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(GoodsFilter::class);
    }
}
