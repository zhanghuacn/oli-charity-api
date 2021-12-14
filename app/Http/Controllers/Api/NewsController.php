<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NewsCollection;
use App\Http\Resources\Api\NewsResource;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;

class NewsController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $paginate = News::filter($request->all())->simplePaginate($request->input('per_page', 10));
        return Response::success(new NewsCollection($paginate));
    }

    public function show(News $news): JsonResponse|JsonResource
    {
        visits($news)->increment();
        return Response::success(new NewsResource($news));
    }
}
