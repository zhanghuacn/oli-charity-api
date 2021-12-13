<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lottery extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasExtendsProperty;
    use Filterable;

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'begin_time',
        'end_time',
        'standard',
        'draw_time',
    ];

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
}
