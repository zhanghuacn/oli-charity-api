<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasCacheProperty;
use App\Traits\HasExtendsProperty;
use App\Traits\HasSettingsProperty;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Overtrue\LaravelSubscribe\Traits\Subscribable;

/**
 * App\Models\Activity
 *
 * @property int $id
 * @property int $charity_id
 * @property string $title 活动标题
 * @property string|null $description 描述
 * @property string|null $content 活动内容
 * @property mixed|null $specialty 特点
 * @property mixed|null $timeline 时间线
 * @property string $location 活动地点
 * @property string $begin_time 活动开始时间
 * @property string $end_time 活动结束时间
 * @property bool $is_visible 是否可见
 * @property array|null $tickets 门票信息
 * @property array|null $extends 扩展信息
 * @property array|null $cache 数据缓存
 * @property string $status 审核状态:等待，通过，拒绝
 * @property string|null $remark 审核备注
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Charity $charity
 * @property-read string $display_status
 * @method static Builder|Activity newModelQuery()
 * @method static Builder|Activity newQuery()
 * @method static Builder|Activity query()
 * @method static Builder|Activity whereBeginTime($value)
 * @method static Builder|Activity whereCache($value)
 * @method static Builder|Activity whereCharityId($value)
 * @method static Builder|Activity whereContent($value)
 * @method static Builder|Activity whereCreatedAt($value)
 * @method static Builder|Activity whereDeletedAt($value)
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereEndTime($value)
 * @method static Builder|Activity whereExtends($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereIsVisible($value)
 * @method static Builder|Activity whereLocation($value)
 * @method static Builder|Activity whereRemark($value)
 * @method static Builder|Activity whereSpecialty($value)
 * @method static Builder|Activity whereStatus($value)
 * @method static Builder|Activity whereTickets($value)
 * @method static Builder|Activity whereTimeline($value)
 * @method static Builder|Activity whereTitle($value)
 * @method static Builder|Activity whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static Builder|Activity orderBySubscribersCount(string $direction = 'desc')
 * @method static Builder|Activity orderBySubscribersCountAsc()
 * @method static Builder|Activity orderBySubscribersCountDesc()
 * @property array|null $images 活动图片
 * @method static Builder|Activity whereImages($value)
 * @property int $is_private 是否私有
 * @method static Builder|Activity whereIsPrivate($value)
 * @property-read Collection|\App\Models\Staff[] $staffs
 * @property-read int|null $staffs_count
 * @property \Illuminate\Support\Fluent $settings 活动设置
 * @property-read int|null $tickets_count
 * @method static Builder|Activity whereSettings($value)
 * @method static Builder|Activity filter(?array $input = null)
 * @method static \Illuminate\Database\Query\Builder|Activity onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Activity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Activity withoutTrashed()
 */
class Activity extends Model
{
    use Filterable;
    use SoftDeletes;
    use HasFactory;
    use HasSettingsProperty;
    use HasCacheProperty;
    use HasExtendsProperty;
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
    public const DEFAULT_SETTINGS = [
        'ticket' => [
            'stock' => 0,
            'price' => 0,
            'sales' => 0,
        ],
        'quota' => [],
    ];


    protected $fillable = [
        'charity_id',
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
        'is_private',
        'images',
        'settings',
        'extends',
        'status',
        'remark',
    ];

    protected $hidden = [
        'is_visible',
    ];

    protected $casts = [
        'images' => 'array',
        'setting' => 'array',
        'cache' => 'array',
        'extends' => 'array',
        'is_visible' => 'bool',
    ];

    protected $attributes = [
        'status' => self::STATUS_WAIT,
    ];

    public function charity(): BelongsTo
    {
        return $this->belongsTo(Charity::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    protected static function booted()
    {
        static::saving(
            function (Activity $activity) {
//                $activity->settings = $activity->settings ?? self::DEFAULT_SETTINGS;
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
