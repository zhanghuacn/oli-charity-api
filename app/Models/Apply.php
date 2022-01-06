<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

/**
 * App\Models\Apply
 *
 * @property Fluent $extends
 * @method static Builder|Apply filter(?array $input = null)
 * @method static Builder|Apply newModelQuery()
 * @method static Builder|Apply newQuery()
 * @method static \Illuminate\Database\Query\Builder|Apply onlyTrashed()
 * @method static Builder|Apply query()
 * @method static \Illuminate\Database\Query\Builder|Apply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Apply withoutTrashed()
 * @mixin Eloquent
 * @property int $id
 * @property int $charity_id 慈善机构
 * @property int $activity_id 活动
 * @property int $user_id 用户
 * @property string $status 审核状态:等待，通过，拒绝
 * @property int|null $reviewer 审核人
 * @property int|null $remark 备注
 * @property string|null $reviewed 审核时间
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Apply whereActivityId($value)
 * @method static Builder|Apply whereCharityId($value)
 * @method static Builder|Apply whereCreatedAt($value)
 * @method static Builder|Apply whereDeletedAt($value)
 * @method static Builder|Apply whereExtends($value)
 * @method static Builder|Apply whereId($value)
 * @method static Builder|Apply whereRemark($value)
 * @method static Builder|Apply whereReviewed($value)
 * @method static Builder|Apply whereReviewer($value)
 * @method static Builder|Apply whereStatus($value)
 * @method static Builder|Apply whereUpdatedAt($value)
 * @method static Builder|Apply whereUserId($value)
 * @method static Builder|Apply paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Apply simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|Apply whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Apply whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Apply whereLike(string $column, string $value, string $boolean = 'and')
 * @property string|null $reviewed_at 审核时间
 * @method static Builder|Apply whereReviewedAt($value)
 * @property-read \App\Models\User $user
 */
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
