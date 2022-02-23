<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\NewsCollection;
use App\Http\Resources\Admin\NewsResource;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Jiannei\Response\Laravel\Support\Facades\Response;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $data = News::filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success(new NewsCollection($data));
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
        $news = new News($request->all());
        Auth::user()->news()->save($news);
        visits($news)->increment();
        return Response::success($news);
    }

    public function show(News $news): JsonResponse|JsonResource
    {
        return Response::success(new NewsResource($news));
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
