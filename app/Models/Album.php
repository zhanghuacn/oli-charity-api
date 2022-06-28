<?php

namespace App\Models;

use App\Traits\ModelFilter;
use DateTimeInterface;
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

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getPathAttribute($value): ?string
    {
        if (!empty($value)) {
            return str_replace(config('filesystems.disks.s3.host'), config('filesystems.disks.s3.cloudfront'), $value);
        }
        return $value;
    }
}
