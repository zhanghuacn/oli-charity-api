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
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

class AuctionController extends Controller
{
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
        abort_if($auction->is_online != true, 422, 'Offline auction');
        if ($auction->is_auction) {
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

    public function orders(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'order_sn' => 'sometimes|string',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = Order::filter($request->all())->where(['user_id' => Auth::id(), 'type' => Order::TYPE_AUCTION])->paginate($request->input('per_page', 15));
        $data->getCollection()->transform(function (Order $order) {
            return [
                'order_sn' => $order->order_sn,
                'name' => $order->orderable->name,
                'images' => $order->orderable->images,
                'start_price' => $order->orderable->price,
                'amount' => $order->total_amount,
                'status' => $order->payment_status,
                'expired_at' => Carbon::parse($order->created_at)->addDays(15)->format('Y-m-d H:i:s'),
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });
        return Response::success($data);
    }

    public function payment(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'order_sn' => 'sometimes|string|exists:orders',
            'payment_method' => 'sometimes|string'
        ]);
        $order = Order::where(['user_id' => Auth::id(), 'type' => Order::TYPE_AUCTION, 'order_sn' => $request->get('order_sn')])->firstOrFail();
        abort_if($order->type != Order::TYPE_AUCTION, 422, 'Order source exception');
        abort_if($order->status == Order::STATUS_CLOSED, 422, 'Order closed');
        if (empty($order->payment_no)) {
            $data = [
                'amount' => $order->amount * 100,
                'currency' => Str::lower(Config::get('cashier.currency')),
                'payment_method_types' => ['card'],
            ];
            if (!empty($request->get('payment_method'))) {
                $payment_method = PaymentMethod::create([
                    'customer' => Auth::user()->stripeId(),
                    'payment_method' => $request->get('payment_method'),
                ], [
                    'stripe_account' => $order->charity->stripe_account_id,
                ]);
                $data['payment_method'] = $payment_method->id;
            }
            $payment_intent = PaymentIntent::create($data, ['stripe_account' => $order->charity->stripe_account_id]);
            $order->payment_no = $payment_intent->id;
            $order->payment_status = Order::STATUS_IN_PAYMENT;
            $order->extends = [
                'client_secret' => $payment_intent->client_secret,
                'payment_method' => $data['payment_method'] ?? null,
            ];
            $order->save();
        }
        return Response::success([
            'stripe_account_id' => $order->charity->stripe_account_id,
            'order_sn' => $order->order_sn,
            'client_secret' => $order->extends['client_secret'],
            'payment_method' => $order->extends['payment_method'] ?? null
        ]);
    }
}
