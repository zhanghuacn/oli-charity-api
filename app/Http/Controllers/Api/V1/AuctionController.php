<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\AuctionBidEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuctionBidRecordCollection;
use App\Http\Resources\Api\AuctionCollection;
use App\Http\Resources\Api\AuctionResource;
use App\Models\Activity;
use App\Models\Auction;
use App\Models\AuctionBidRecord;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        Gate::authorize('check-ticket', $auction->activity);
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
        Gate::authorize('check-ticket', $auction->activity);
        $request->validate([
            'amount' => 'required|numeric|gt:' . $auction->current_bid_price,
        ]);
        abort_if($auction->end_time < now(), 422, 'Auction is over');
        if($auction->is_auction){
            $amount = $request->get('amount');
            DB::transaction(function () use ($amount, $auction) {
                $record = new AuctionBidRecord();
                $record->price = $auction->current_bid_price;
                $record->bid_price = $amount;
                $record->user_id = Auth::id();
                $auction->bidRecord()->save($record);
                $auction->current_bid_price = $amount;
                $auction->current_bid_user_id = Auth::id();
                $auction->current_bid_time = now();
                $auction->save();
            });
            AuctionBidEvent::dispatch(AuctionBidRecord::first());
        }
        return Response::success();
    }
}
