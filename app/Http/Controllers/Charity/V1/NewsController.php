<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
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
        $data = News::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'title' => 'required|string',
            'thumb' => 'required|string',
            'keyword' => 'sometimes|string',
            'source' => 'sometimes|string',
            'description' => 'sometimes|string',
            'content' => 'required|string',
            'status' => 'sometimes|in:ENABLE,DISABLE',
            'sort' => 'sometimes|numeric|min:0',
        ]);
        $news = new News($request->all());
        Auth::user()->news()->save($news);
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
