<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apply extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelFilter;
    use HasExtendsProperty;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    public const STATUSES = [
        self::STATUS_WAIT => 'WAITING FOR REVIEW',
        self::STATUS_PASSED => 'APPROVED',
        self::STATUS_REFUSE => 'AUDIT REJECT',
    ];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'user_id',
        'status',
        'reviewer',
        'remark',
        'extends',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'extends' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
