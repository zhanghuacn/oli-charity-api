<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasExtendsProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ActivityApplyRecord
 *
 * @property \Illuminate\Support\Fluent $extends
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord newQuery()
 * @method static \Illuminate\Database\Query\Builder|ActivityApplyRecord onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord query()
 * @method static \Illuminate\Database\Query\Builder|ActivityApplyRecord withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ActivityApplyRecord withoutTrashed()
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
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereReviewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereReviewer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityApplyRecord whereUserId($value)
 */
class ActivityApplyRecord extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;
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
