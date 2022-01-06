<?php

namespace App\Models;

use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use App\Traits\ModelFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

/**
 * App\Models\Goods
 *
 * @property int $id
 * @property string $type 商品类型
 * @property string $name 商品名称
 * @property string|null $description 商品描述
 * @property string|null $content 商品内容
 * @property int $stock 库存数量
 * @property string $status 状态
 * @property Fluent $images 商品图片
 * @property array|null $tag 商品标签
 * @property Fluent $cache 数据缓存
 * @property Fluent $extends 扩展信息
 * @property string $goodsable_type
 * @property int $goodsable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Model|Eloquent $originable
 * @method static \Illuminate\Database\Eloquent\Builder|Goods filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods newQuery()
 * @method static Builder|Goods onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods query()
 * @method static \Illuminate\Database\Eloquent\Builder|Goods simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereGoodsableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereGoodsableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereLike(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereUpdatedAt($value)
 * @method static Builder|Goods withTrashed()
 * @method static Builder|Goods withoutTrashed()
 * @mixin Eloquent
 * @property string|null $price
 * @property-read Model|Eloquent $goodsable
 * @property-read Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|Goods wherePrice($value)
 * @property int $charity_id 机构
 * @property int $activity_id 活动
 * @property-read \App\Models\Activity $activity
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Goods whereCharityId($value)
 */
class Goods extends Model
{
    use HasFactory;
    use Filterable;
    use HasImagesProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use ModelFilter;
    use SoftDeletes;

    public const STATUS_ENABLE = 'ENABLE';
    public const STATUS_DISABLE = 'DISABLE';

    public const DEFAULT_IMAGES = [];
    public const DEFAULT_EXTENDS = [
        'sale_num' => 0,
        'sale_income' => 0,
    ];

    protected $fillable = [
        'charity_id',
        'activity_id',
        'name',
        'description',
        'content',
        'price',
        'stock',
        'status',
        'images',
        'extends',
        'cache',
        'goodsable_type',
        'goodsable_id',
    ];

    protected $hidden = [
        'charity_id',
        'activity_id',
        'extends',
        'cache',
        'prizeable_type',
        'prizeable_id',
        'deleted_at',
    ];

    protected $casts = [
        'price' => 'float',
        'images' => 'array',
        'extends' => 'array',
        'cache' => 'array',
    ];

    public function goodsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }
}
