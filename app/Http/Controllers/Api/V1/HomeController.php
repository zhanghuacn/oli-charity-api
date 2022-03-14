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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController extends Controller
{
    public function explore(): JsonResponse|JsonResource
    {
        $events = Activity::where([['is_visible', '=', true], ['end_time', '>=', Carbon::now()]])->orderBy('begin_time')->fresh()->limit(10)->get();
        $peoples = User::orderByAmount()->limit(10)->get();
        $news = visits(News::class)->fresh()->top(10);
        $charities = visits(Charity::class)->fresh()->top(10);
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
        $keyword = $request->get('keyword');
        return Response::success([
            'charities' => new CharityCollection(Charity::search($keyword)->get()),
            'events' => new ActivityCollection(Activity::search($keyword)->get()),
            'news' => new NewsCollection(News::search($keyword)->get()),
            'peoples' => new UserCollection(User::search($keyword)->get()),
            'sponsors' => new SponsorCollection(Sponsor::search($keyword)->get()),
        ]);
    }
}
