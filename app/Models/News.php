<?php

namespace App\Models;

use App\ModelFilters\NewsFilter;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\News
 *
 * @property int $id
 * @property string $title 名称
 * @property string|null $thumb 缩略图
 * @property string|null $keyword 关键词
 * @property string|null $source 来源
 * @property string|null $description 摘要
 * @property string|null $content 内容
 * @property string $status 状态: 上架,下架
 * @property string $newsable_type
 * @property int $newsable_id
 * @property string $published_at 发布时间
 * @property int $sort 排序
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News query()
 * @method static \Illuminate\Database\Eloquent\Builder|News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNewsableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNewsableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|News filter(?array $input = null)
 * @method static \Illuminate\Database\Query\Builder|News onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|News withTrashed()
 * @method static \Illuminate\Database\Query\Builder|News withoutTrashed()
 */
class News extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'thumb',
        'keyword',
        'source',
        'description',
        'content',
        'status',
        'published_at',
        'sort',
    ];

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(NewsFilter::class);
    }
}
