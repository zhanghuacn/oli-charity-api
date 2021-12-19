<?php

namespace App\Search;

use Algolia\ScoutExtended\Searchable\Aggregator;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\News;
use App\Models\Sponsor;
use App\Models\User;
use Laravel\Scout\Searchable;

class Search extends Aggregator
{
    /**
     * The names of the models that should be aggregated.
     *
     * @var string[]
     */
    protected $models = [
        Charity::class,
        Activity::class,
        News::class,
        User::class,
        Sponsor::class,
    ];

    public function shouldBeSearchable()
    {
        if (array_key_exists(Searchable::class, class_uses($this->model))) {
            return $this->model->shouldBeSearchable();
        }
    }
}
