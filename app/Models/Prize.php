<?php

namespace App\Models;

use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;
use function array_replace_recursive;
use function constant;
use function defined;
use function json_decode;

/**
 * App\Models\Prize
 *
 * @property int $id
 * @property int $charity_id 慈善机构
 * @property int $activity_id 活动
 * @property string $name 名称
 * @property string|null $description 描述
 * @property int $num 奖品数量
 * @property mixed|null $winners 中奖榜单
 * @property Fluent $extends 扩展信息
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Activity $activity
 * @property-read Charity $charity
 * @property-read Lottery $lottery
 * @method static \Illuminate\Database\Eloquent\Builder|Prize filter(?array $input = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Prize newQuery()
 * @method static Builder|Prize onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Prize query()
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereWinners($value)
 * @method static Builder|Prize withTrashed()
 * @method static Builder|Prize withoutTrashed()
 * @mixin Eloquent
 * @property int $lottery_id 抽奖
 * @property int $goods_id 商品
 * @property Fluent $images
 * @property-read \App\Models\Goods $goods
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereLotteryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereLike(string $column, string $value, string $boolean = 'and')
 * @property mixed|null $cache 数据缓存
 * @property string $prizeable_type
 * @property int $prizeable_id
 * @property-read Model|\Eloquent $prizeable
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize wherePrizeableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Prize wherePrizeableType($value)
 * @property string|null $price
 * @method static \Illuminate\Database\Eloquent\Builder|Prize wherePrice($value)
 */
class Prize extends Model
{
    use HasFactory;
    use HasImagesProperty;
    use HasExtendsProperty;
    use Filterable;
    use ModelFilter;
    use SoftDeletes;

    public const STATUS_ENABLE = 'ENABLE';
    public const STATUS_DISABLE = 'DISABLE';

    public const DEFAULT_IMAGES = [];
    public const DEFAULT_EXTENDS = [];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'lottery_id',
        'name',
        'images',
        'description',
        'num',
        'winners',
        'draw_time',
        'status',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'lottery_id',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
        'deleted_at',
    ];

    protected $casts = [
        'images' => 'array',
        'winners' => 'array',
        'extends' => 'array',
        'draw_time' => 'datetime:Y-m-d H:i:s',
    ];

    public function prizeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class);
    }

    public function setWinnersAttribute(array $winners)
    {
        $this->attributes['winners'] = json_encode($winners);
    }

    public function getWinnersAttribute(): Fluent
    {
        return new Fluent($this->getWinners());
    }

    public function getWinners(): array
    {
        return array_replace_recursive(defined('static::DEFAULT_WINNERS') ?
            constant('static::DEFAULT_WINNERS') : [], json_decode($this->attributes['winners'] ?? '{}', true) ?? []);
    }
}
