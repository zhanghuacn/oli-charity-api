<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Lottery
 *
 * @property int $id
 * @property int $charity_id 慈善机构
 * @property int $activity_id 活动
 * @property string $name 名称
 * @property string|null $description 描述
 * @property string $begin_time 开始时间
 * @property string $end_time 结束时间
 * @property string $standard_amount 达标金额
 * @property string $draw_time 开奖时间
 * @property \Illuminate\Support\Fluent $extends 扩展信息
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Activity $activity
 * @property-read \App\Models\Charity $charity
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Prize[] $prizes
 * @property-read int|null $prizes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery newQuery()
 * @method static \Illuminate\Database\Query\Builder|Lottery onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereDrawTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereStandardAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Lottery withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Lottery withoutTrashed()
 * @mixin \Eloquent
 * @property \Illuminate\Support\Fluent $images 图片
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Lottery whereLike(string $column, string $value, string $boolean = 'and')
 */
class Lottery extends Model
{
    use HasFactory;
    use HasImagesProperty;
    use HasExtendsProperty;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'begin_time',
        'end_time',
        'standard_amount',
        'draw_time',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'images' => 'array',
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

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class);
    }
}
