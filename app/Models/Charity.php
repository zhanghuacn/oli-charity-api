<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Hash;
use Overtrue\LaravelSubscribe\Traits\Subscribable;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Activity[] $activities
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static Builder|Charity orderBySubscribersCount(string $direction = 'desc')
 * @method static Builder|Charity orderBySubscribersCountAsc()
 * @method static Builder|Charity orderBySubscribersCountDesc()
 */
class Charity extends Model
{
    use HasFactory;
    use Subscribable;

    public const STATUS_WAIT = 'WAIT';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_REFUSE = 'REFUSE';

    public const STATUSES = [
        self::STATUS_WAIT => 'WAITING FOR REVIEW',
        self::STATUS_PASSED => 'APPROVED',
        self::STATUS_REFUSE => 'AUDIT REJECT',
    ];

    // 默认缓存信息
    public const DEFAULT_CACHE = [
    ];

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

    protected static function booted()
    {
        static::saving(
            function (Charity $charity) {
                $charity->cache = $charity->cache ?? self::DEFAULT_CACHE;
            }
        );
    }

    public function getDisplayStatusAttribute(): string
    {
        return self::STATUSES[$this->status ?? self::STATUS_WAIT];
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }
}
