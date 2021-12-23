<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\ModelTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Apply
 *
 * @property \Illuminate\Support\Fluent $extends
 * @method static \Illuminate\Database\Eloquent\Builder|Apply filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Apply newQuery()
 * @method static \Illuminate\Database\Query\Builder|Apply onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Apply query()
 * @method static \Illuminate\Database\Query\Builder|Apply withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Apply withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property int $charity_id 慈善机构
 * @property int $activity_id 活动
 * @property int $user_id 用户
 * @property string $status 审核状态:等待，通过，拒绝
 * @property int|null $reviewer 审核人
 * @property int|null $remark 备注
 * @property string|null $reviewed 审核时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereReviewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Apply whereLike(string $column, string $value, string $boolean = 'and')
 */
class Apply extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
    use ModelTrait;
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
    ];

    protected $casts = [
        'extends' => 'array',
    ];
}
