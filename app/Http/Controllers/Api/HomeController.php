<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\CharityCollection;
use App\Http\Resources\Api\NewsCollection;
use App\Http\Resources\Api\UserCollection;
use App\Models\Activity;
use App\Models\Charity;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class HomeController extends Controller
{
    public function explore(): JsonResponse|JsonResource
    {
        $data = [
            'events' => new ActivityCollection(Activity::limit(5)->get()),
            'peoples' => new UserCollection(User::limit(5)->get()),
            'news' => new NewsCollection(News::limit(5)->get()),
            'charities' => new CharityCollection(Charity::limit(5)->get()),
        ];
        return Response::success($data);
    }

    public function search(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'required|string',
        ]);
        $charities = Charity::search($request->keyword)->get();
        $events = Activity::search($request->keyword)->get();
        $news = News::search($request->keyword)->get();
        $peoples = User::search($request->keyword)->get();
        return Response::success([
            'charities' => new CharityCollection($charities),
            'events' => new ActivityCollection($events),
            'news' => new NewsCollection($news),
            'peoples' => new UserCollection($peoples),
        ]);
    }
}
