<?php

namespace App\Models;

use App\ModelFilters\SponsorFilter;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Favorite;
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
 * @property Fluent $extends 扩展信息
 * @property Fluent $cache 数据缓存
 * @property string $status 审核状态:等待，通过，拒绝
 * @property string|null $remark 审核备注
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Goods[] $goods
 * @property-read int|null $goods_count
 * @property-read Collection|User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static Builder|Sponsor newModelQuery()
 * @method static Builder|Sponsor newQuery()
 * @method static \Illuminate\Database\Query\Builder|Sponsor onlyTrashed()
 * @method static Builder|Sponsor orderBySubscribersCount(string $direction = 'desc')
 * @method static Builder|Sponsor orderBySubscribersCountAsc()
 * @method static Builder|Sponsor orderBySubscribersCountDesc()
 * @method static Builder|Sponsor query()
 * @method static Builder|Sponsor whereAddress($value)
 * @method static Builder|Sponsor whereBackdrop($value)
 * @method static Builder|Sponsor whereCache($value)
 * @method static Builder|Sponsor whereContact($value)
 * @method static Builder|Sponsor whereCreatedAt($value)
 * @method static Builder|Sponsor whereCredentials($value)
 * @method static Builder|Sponsor whereDeletedAt($value)
 * @method static Builder|Sponsor whereDescription($value)
 * @method static Builder|Sponsor whereDocuments($value)
 * @method static Builder|Sponsor whereEmail($value)
 * @method static Builder|Sponsor whereExtends($value)
 * @method static Builder|Sponsor whereId($value)
 * @method static Builder|Sponsor whereIntroduce($value)
 * @method static Builder|Sponsor whereLogo($value)
 * @method static Builder|Sponsor whereMobile($value)
 * @method static Builder|Sponsor whereName($value)
 * @method static Builder|Sponsor wherePhone($value)
 * @method static Builder|Sponsor whereRemark($value)
 * @method static Builder|Sponsor whereStatus($value)
 * @method static Builder|Sponsor whereUpdatedAt($value)
 * @method static Builder|Sponsor whereWebsite($value)
 * @method static \Illuminate\Database\Query\Builder|Sponsor withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Sponsor withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read Collection|Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @method static Builder|Sponsor filter(array $input = [], $filter = null)
 * @method static Builder|Sponsor paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Sponsor simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|Sponsor whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Sponsor whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Sponsor whereLike(string $column, string $value, string $boolean = 'and')
 */
class Sponsor extends Model
{
    use SoftDeletes;
    use HasFactory;
    use HasCacheProperty;
    use HasExtendsProperty;
    use Favoriteable;
    use Filterable;
    use ModelFilter;
    use Searchable;

    public const GUARD_NAME = 'sponsor';
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

    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

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
