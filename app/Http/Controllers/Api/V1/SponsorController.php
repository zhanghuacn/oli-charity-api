<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityCollection;
use App\Http\Resources\Api\SponsorCollection;
use App\Http\Resources\Api\SponsorResource;
use App\Models\Goods;
use App\Models\Sponsor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Jiannei\Response\Laravel\Support\Facades\Response;
use function collect;
use function visits;

class SponsorController extends Controller
{
    public function index(Request $request): JsonResponse|JsonResource
    {
        $paginate = Sponsor::filter($request->all())->simplePaginate($request->input('per_page', 15));
        return Response::success(new SponsorCollection($paginate));
    }

    public function show(Sponsor $sponsor): JsonResponse|JsonResource
    {
        visits($sponsor)->increment();
        return Response::success(new SponsorResource($sponsor));
    }

    public function goods(Sponsor $sponsor): JsonResponse|JsonResource
    {
        $goods = $sponsor->goods->transform(function (Goods $goods) {
            return [
                'id' => $goods->id,
                'name' => $goods->name,
                'image' => collect($goods->images)->first(),
                'description' => $goods->description,
                'events' => new ActivityCollection($goods->activities)
            ];
        });
        return Response::success($goods);
    }
}
