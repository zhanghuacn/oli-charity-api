<?php

namespace App\Models;

use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class News extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;
    use Searchable;
    use ModelFilter;

    protected $fillable = [
        'title',
        'thumb',
        'banner',
        'keyword',
        'source',
        'description',
        'content',
        'status',
        'published_at',
        'sort',
    ];

    protected $hidden = [
        'newsable_type',
        'newsable_id',
        'deleted_at',
    ];

    public function newsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function shouldBeSearchable(): bool
    {
        return $this->published_at != null;
    }
}
