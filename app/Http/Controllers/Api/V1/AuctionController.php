<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuctionBidRecordCollection;
use App\Http\Resources\Api\AuctionCollection;
use App\Http\Resources\Api\AuctionResource;
use App\Models\Activity;
use App\Models\Auction;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;

class AuctionController extends Controller
{

    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $activity);
        $data = $activity->auctions()->withCount('bidRecord')->get();
        return Response::success(new AuctionCollection($data));
    }

    public function show(Auction $auction): JsonResponse|JsonResource
    {
        Gate::authorize('check-ticket', $auction->activity);
        return Response::success(new AuctionResource($auction));
    }

    public function history(Request $request, Auction $auction): JsonResponse|JsonResource
    {
        $request->validate([
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $auction->bidRecord()->orderByDesc('id')
            ->paginate($request->input('per_page', 15));
        return Response::success(new AuctionBidRecordCollection($data));
    }

    public function bid(Request $request, Auction $auction): JsonResponse|JsonResource
    {
        return Response::success();
    }
}
