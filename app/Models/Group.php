<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use DateTimeInterface;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;
    use HasExtendsProperty;

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'num',
        'owner_id',
        'extends'
    ];

    protected $casts = [
        'extends' => 'array',
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

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'owner_id');
    }

    public function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
