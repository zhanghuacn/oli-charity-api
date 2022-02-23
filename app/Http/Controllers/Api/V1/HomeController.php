<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\NewsCollection;
use App\Http\Resources\Api\SponsorCollection;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\News;
use App\Models\Order;
use App\Models\Sponsor;
use App\Models\User;
use App\Search\Search;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function collect;

class HomeController extends Controller
{
    public function explore(): JsonResponse|JsonResource
    {
        $events = Activity::where([['is_visible', '=', true], ['end_time', '>=', Carbon::now()->tz(config('app.timezone'))]])->orderBy('begin_time')->limit(10)->get();
        $peoples = User::orderByAmount()->limit(10)->get();
        $news = visits(News::class)->top(10);
        $charities = visits(Charity::class)->top(10);
        $data = [
            'events' => new ActivityCollection($events),
            'peoples' => new UserCollection($peoples),
            'news' => new NewsCollection($news),
            'charities' => new CharityCollection($charities),
        ];
        return Response::success($data);
    }

    public function search(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);
        $data = [
            'charities' => [],
            'activities' => [],
            'news' => [],
            'users' => [],
            'sponsors' => [],
        ];
        $searches = Search::search($request->keyword)->get();
        foreach ($searches as $model) {
            switch (get_class($model)) {
                case Charity::class:
                    $data['charities'][] = $model;
                    break;
                case Activity::class:
                    $data['activities'][] = $model;
                    break;
                case News::class:
                    $data['news'][] = $model;
                    break;
                case User::class:
                    $data['users'][] = $model;
                    break;
                case Sponsor::class:
                    $data['sponsors'][] = $model;
                    break;
                default:
                    throw new \Exception('Unexpected value');
            }
        }
        return Response::success([
            'charities' => new CharityCollection(collect($data['charities'])->whereNull('deleted_at')->all()),
            'events' => new ActivityCollection(collect($data['activities'])->whereNull('deleted_at')->all()),
            'news' => new NewsCollection(collect($data['news'])->whereNull('deleted_at')->all()),
            'peoples' => new UserCollection(collect($data['users'])->whereNull('deleted_at')->all()),
            'sponsors' => new SponsorCollection(collect($data['sponsors'])->whereNull('deleted_at')->all()),
        ]);
    }
}
