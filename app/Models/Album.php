<?php

namespace App\Models;

use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;

    protected $fillable = [
        'path',
        'user_id',
        'is_visible',
        'albumable_type',
        'albumable_id',
    ];

    protected $hidden = [
        'albumable_type',
        'albumable_id',
        'is_visible',
        'deleted_at',
    ];

    protected $casts = [
        'is_visible' => 'bool',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function albumable(): MorphTo
    {
        return $this->morphTo();
    }
}
