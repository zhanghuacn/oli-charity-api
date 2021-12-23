<?php

namespace App\Models;

use App\ModelFilters\SponsorFilter;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

/**
 * App\Models\Sponsor
 *
 * @property int $id
 * @property string $name 名称
 * @property string $logo logo
 * @property string $backdrop 背景图
 * @property string $website 网站
 * @property string $description 描述
 * @property string $introduce 描述
 * @property array|null $credentials 证件
 * @property array|null $documents 其他文件
 * @property string $contact 联系人
 * @property string $phone 联系人电话
 * @property string|null $mobile 联系人座机
 * @property string|null $email 邮箱
 * @property string|null $address 地址
 * @property \Illuminate\Support\Fluent $extends 扩展信息
 * @property \Illuminate\Support\Fluent $cache 数据缓存
 * @property string $status 审核状态:等待，通过，拒绝
 * @property string|null $remark 审核备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Goods[] $goods
 * @property-read int|null $goods_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor newQuery()
 * @method static \Illuminate\Database\Query\Builder|Sponsor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor orderBySubscribersCount(string $direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor orderBySubscribersCountAsc()
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor orderBySubscribersCountDesc()
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereBackdrop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereCredentials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereIntroduce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereWebsite($value)
 * @method static \Illuminate\Database\Query\Builder|Sponsor withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Sponsor withoutTrashed()
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor filter(array $input = [], $filter = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Sponsor whereLike(string $column, string $value, string $boolean = 'and')
 */
class Sponsor extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriteable;
    use Filterable;
    use ModelTrait;
    use Searchable;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    // 默认缓存信息
    public const DEFAULT_CACHE = [];

    protected $fillable = [
        'name',
        'logo',
        'website',
        'description',
        'introduce',
        'credentials',
        'documents',
        'contact',
        'phone',
        'mobile',
        'email',
        'address',
        'cache',
        'extends',
        'status',
        'remark',
    ];

    protected $hidden = [
        'is_visible',
    ];

    protected $casts = [
        'credentials' => 'array',
        'documents' => 'array',
        'cache' => 'array',
        'extends' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    public function goods(): MorphMany
    {
        return $this->morphMany(Goods::class, 'goodsable');
    }

    protected static function booted()
    {
        static::saving(
            function (Sponsor $sponsor) {
            }
        );
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function searchableAs(): string
    {
        return 'sponsors_index';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status == self::STATUS_PASSED;
    }
}
