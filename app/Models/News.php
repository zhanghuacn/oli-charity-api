<?php

namespace App\Models;

use App\ModelFilters\NewsFilter;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static Builder|News newModelQuery()
 * @method static Builder|News newQuery()
 * @method static Builder|News query()
 * @method static Builder|News whereContent($value)
 * @method static Builder|News whereCreatedAt($value)
 * @method static Builder|News whereDeletedAt($value)
 * @method static Builder|News whereDescription($value)
 * @method static Builder|News whereId($value)
 * @method static Builder|News whereKeyword($value)
 * @method static Builder|News whereNewsableId($value)
 * @method static Builder|News whereNewsableType($value)
 * @method static Builder|News wherePublishedAt($value)
 * @method static Builder|News whereSort($value)
 * @method static Builder|News whereSource($value)
 * @method static Builder|News whereStatus($value)
 * @method static Builder|News whereThumb($value)
 * @method static Builder|News whereTitle($value)
 * @method static Builder|News whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|News filter(?array $input = null)
 * @method static \Illuminate\Database\Query\Builder|News onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|News withTrashed()
 * @method static \Illuminate\Database\Query\Builder|News withoutTrashed()
 * @method static Builder|News paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|News simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|News whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|News whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|News whereLike(string $column, string $value, string $boolean = 'and')
 */
class News extends Model
{
    use HasFactory;
    use Filterable;
    use SoftDeletes;
    use Searchable;

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

    public function newsable(): MorphTo
    {
        return $this->morphTo();
    }

    public function visits(): Relation
    {
        return visits($this)->relation();
    }

    public function modelFilter(): ?string
    {
        return $this->provideFilter(NewsFilter::class);
    }

    public function searchableAs(): string
    {
        return 'news_index';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->published_at != null;
    }
}
