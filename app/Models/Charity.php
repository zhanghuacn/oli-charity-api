<?php

namespace App\Models;

use App\ModelFilters\ActivityFilter;
use App\ModelFilters\CharityFilter;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\ModelTrait;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Overtrue\LaravelFavorite\Traits\Favoriteable;
use Spatie\Permission\Models\Role;

/**
 * App\Models\Charity
 *
 * @property-read string $display_status
 * @method static Builder|Charity newModelQuery()
 * @method static Builder|Charity newQuery()
 * @method static Builder|Charity query()
 * @mixin Eloquent
 * @property int $id
 * @property string $name 名称
 * @property string $logo logo
 * @property string $website 网站
 * @property string $description 描述
 * @property string $introduce 描述
 * @property int $staff_num 员工数量
 * @property array|null $credentials 证件
 * @property array|null $documents 其他文件
 * @property string $contact 联系人
 * @property string $phone 联系人电话
 * @property string|null $mobile 联系人座机
 * @property string|null $email 邮箱
 * @property string|null $address 地址
 * @property string|null $stripe_account stripe管理账号
 * @property bool $is_visible 是否可见
 * @property array|null $extends 扩展信息
 * @property array|null $cache 数据缓存
 * @property string $status 审核状态:等待，通过，拒绝
 * @property string|null $remark 审核备注
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static Builder|Charity whereAddress($value)
 * @method static Builder|Charity whereCache($value)
 * @method static Builder|Charity whereContact($value)
 * @method static Builder|Charity whereCreatedAt($value)
 * @method static Builder|Charity whereCredentials($value)
 * @method static Builder|Charity whereDeletedAt($value)
 * @method static Builder|Charity whereDescription($value)
 * @method static Builder|Charity whereDocuments($value)
 * @method static Builder|Charity whereEmail($value)
 * @method static Builder|Charity whereExtends($value)
 * @method static Builder|Charity whereId($value)
 * @method static Builder|Charity whereIntroduce($value)
 * @method static Builder|Charity whereIsVisible($value)
 * @method static Builder|Charity whereLogo($value)
 * @method static Builder|Charity whereMobile($value)
 * @method static Builder|Charity whereName($value)
 * @method static Builder|Charity wherePhone($value)
 * @method static Builder|Charity whereRemark($value)
 * @method static Builder|Charity whereStaffNum($value)
 * @method static Builder|Charity whereStatus($value)
 * @method static Builder|Charity whereStripeAccount($value)
 * @method static Builder|Charity whereUpdatedAt($value)
 * @method static Builder|Charity whereWebsite($value)
 * @property-read Collection|User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static Builder|Charity orderBySubscribersCount(string $direction = 'desc')
 * @method static Builder|Charity orderBySubscribersCountAsc()
 * @method static Builder|Charity orderBySubscribersCountDesc()
 * @method static Builder|Charity filter(?array $input = null)
 * @method static \Illuminate\Database\Query\Builder|Charity onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Charity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Charity withoutTrashed()
 * @property string $backdrop 背景图
 * @property-read Collection|\App\Models\Goods[] $goods
 * @property-read int|null $goods_count
 * @property-read Collection|\App\Models\Order[] $orders
 * @property-read int|null $orders_count
 * @method static Builder|Charity whereBackdrop($value)
 * @property-read Collection|\App\Models\User[] $favoriters
 * @property-read int|null $favoriters_count
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @method static Builder|Charity paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Charity simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|Charity whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Charity whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|Charity whereLike(string $column, string $value, string $boolean = 'and')
 */
class Charity extends Model
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

    public const STATUSES = [
        self::STATUS_WAIT => 'WAITING FOR REVIEW',
        self::STATUS_PASSED => 'APPROVED',
        self::STATUS_REFUSE => 'AUDIT REJECT',
    ];

    // 默认缓存信息
    public const DEFAULT_CACHE = [];

    protected $fillable = [
        'name',
        'logo',
        'website',
        'description',
        'introduce',
        'staff_num',
        'credentials',
        'documents',
        'contact',
        'phone',
        'mobile',
        'email',
        'address',
        'stripe_account',
        'is_visible',
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
        'is_visible' => 'bool',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function goods(): MorphMany
    {
        return $this->morphMany(Goods::class, 'goodsable');
    }

    public function orders(): MorphMany
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    protected static function booted()
    {
        parent::boot();
        self::created(function (Charity $charity) {
        });
    }

    public function getDisplayStatusAttribute(): string
    {
        return self::STATUSES[$this->status ?? self::STATUS_WAIT];
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function searchableAs(): string
    {
        return 'charities_index';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status == self::STATUS_PASSED;
    }
}
