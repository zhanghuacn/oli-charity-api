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
use App\Models\Sponsor;
use App\Models\User;
use App\Search\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function collect;

class HomeController extends Controller
{
    public function explore(): JsonResponse|JsonResource
    {
        $data = [
            'events' => new ActivityCollection(Activity::whereIsOnline(true)->limit(5)->get()),
            'peoples' => new UserCollection(User::limit(5)->get()),
            'news' => new NewsCollection(News::limit(5)->get()),
            'charities' => new CharityCollection(Charity::whereStatus(Charity::STATUS_PASSED)->limit(5)->get()),
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
            'charities' => new CharityCollection(collect($data['charities'])),
            'events' => new ActivityCollection(collect($data['activities'])),
            'news' => new NewsCollection(collect($data['news'])),
            'peoples' => new UserCollection(collect($data['users'])),
            'sponsors' => new SponsorCollection(collect($data['sponsors'])),
        ]);
    }
}
