<?php

namespace App\Models;

use App\ModelFilters\GoodsFilter;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasImagesProperty;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
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
 * @property-read Model|\Eloquent $goodsable
 * @property-read Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|Goods wherePrice($value)
 */
class Goods extends Model
{
    use HasFactory;
    use Filterable;
    use HasImagesProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
    use SoftDeletes;

    public const TYPE_LOTTERY = 'LOTTERY';
    public const TYPE_BAZAARS = 'BAZAARS';

    public const STATUS_ENABLE = 'ENABLE';
    public const STATUS_DISABLE = 'DISABLE';

    protected $fillable = [
        'type',
        'description',
        'content',
        'images',
        'tag',
        'stock',
        'status',
        'goodsable_type',
        'goodsable_id',
    ];

    protected $casts = [
        'images' => 'array',
        'tag' => 'array',
    ];

    public function goodsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(GoodsFilter::class);
    }
}
