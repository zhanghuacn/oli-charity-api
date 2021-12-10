<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Charity $charity
 * @property-read string $display_status
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCache($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereExtends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereIsVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereSpecialty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereTickets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereTimeline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Activity orderBySubscribersCount(string $direction = 'desc')
 * @method static \Illuminate\Database\Eloquent\Builder|Activity orderBySubscribersCountAsc()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity orderBySubscribersCountDesc()
 * @property array|null $images 活动图片
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereImages($value)
 */
class Activity extends Model
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
    public const DEFAULT_TICKETS = [
        'stock' => 0,
        'price' => 0,
        'sales' => 0,
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
        'extends',
        'status',
        'remark',
    ];

    protected $hidden = [
        'is_visible',
    ];

    protected $casts = [
        'images' => 'array',
        'tickets' => 'array',
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

    protected static function booted()
    {
        static::saving(
            function (Activity $activity) {
                $activity->tickets = $activity->tickets ?? self::DEFAULT_TICKETS;
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
