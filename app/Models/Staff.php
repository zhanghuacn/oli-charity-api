<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Staff
 *
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Charity $charity
 * @property \Illuminate\Support\Fluent $extends
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Staff filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $charity_id
 * @property int $activity_id
 * @property int $user_id
 * @property string $position 职位: 主持人, 工作人员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereUserId($value)
 */
class Staff extends Model
{
    use HasFactory;
    use Filterable;
    use HasExtendsProperty;

    protected $table = 'staffs';

    public const STAFF_EMPLOYEE = 'EMPLOYEE';
    public const STAFF_HOST = 'HOST';

    protected $fillable = [
        'charity_id',
        'activity_id',
        'user_id',
        'position',
        'extends'
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
