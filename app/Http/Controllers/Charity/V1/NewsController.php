<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Models\Charity;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'sometimes|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = News::filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'title' => 'required|string',
            'thumb' => 'sometimes|string',
            'banner' => 'sometimes|string',
            'keyword' => 'sometimes|string',
            'source' => 'sometimes|string',
            'description' => 'sometimes|string',
            'content' => 'required|string',
            'status' => 'sometimes|in:ENABLE,DISABLE',
            'sort' => 'sometimes|numeric|min:0',
        ]);
        $news = Charity::find(getPermissionsTeamId())->news()->save(new News($request->all()));
        visits($news)->increment();
        return Response::success($news);
    }

    public function show(News $news): JsonResponse|JsonResource
    {
        return Response::success($news);
    }

    public function update(Request $request, News $news): JsonResponse|JsonResource
    {
        $request->validate([
            'title' => 'sometimes|string',
            'thumb' => 'sometimes|string',
            'keyword' => 'sometimes|string',
            'source' => 'sometimes|string',
            'description' => 'sometimes|string',
            'content' => 'sometimes|string',
            'status' => 'sometimes|in:ENABLE,DISABLE',
            'sort' => 'sometimes|numeric|min:0',
        ]);
        $news->update($request->all());
        return Response::success($news);
    }


    public function destroy(News $news): JsonResponse|JsonResource
    {
        $news->delete();
        return Response::success();
    }
}
