<?php

namespace App\Http\Controllers\Charity\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Charity\ActivityCollection;
use App\Http\Resources\Charity\ActivityResource;
use App\Models\Activity;
use App\Models\Album;
use App\Models\Auction;
use App\Models\Gift;
use App\Models\Goods;
use App\Models\Lottery;
use App\Models\Order;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;

class ActivityController extends Controller
{
    private ActivityService $activityService;

    public function __construct(ActivityService $activityService)
    {
        parent::__construct();
        $this->activityService = $activityService;
    }

    public function index(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'keyword' => 'sometimes|string',
            'filter' => 'sometimes|in:ACTIVE,PAST',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $activities = Activity::withCount(['applies', 'tickets'])->filter($request->all())->paginate($request->input('per_page', 15));
        return Response::success(new ActivityCollection($activities));
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function views(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        return Response::success($this->getDetails($activity));
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function tickets(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $request->validate([
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
            'code' => 'nullable|string',
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $activity->tickets()->filter($request->all())->with(['user', 'group'])
            ->paginate($request->input('per_page', 15));
        $data->getCollection()->transform(function (Ticket $ticket) {
            return [
                'id' => $ticket->id,
                'uid' => $ticket->user->id,
                'avatar' => $ticket->user->avatar,
                'name' => $ticket->user->name,
                'email' => $ticket->user->email,
                'phone' => $ticket->user->phone,
                'group' => optional($ticket->group)->name,
                'ticket' => $ticket->code,
                'amount' => $ticket->amount,
            ];
        });
        return Response::success($data);
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function seatAllocation(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $request->validate([
            'config' => 'required|string',
            'seats' => 'sometimes|array',
            'seats.*.id' => 'required|integer|exists:tickets,id,activity_id,' . $activity->id,
            'seats.*.seat_num' => 'required|string|distinct',
        ]);
        try {
            DB::transaction(function () use ($request, $activity) {
                $activity->update(['settings->seat_config' => $request->get('config')]);
                collect($request->get('seats'))->each(function ($item) {
                    Ticket::where(['id' => $item['id']])->update(['seat_num' => $item['seat_num']]);
                });
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
        return Response::success();
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function seatConfig(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $data = [
            'seat_config' => $activity->settings['seat_config'],
            'tickets' => $activity->tickets()->with(['group', 'user'])->get()->transform(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'avatar' => optional($ticket->user)->avatar,
                    'name' => optional($ticket->user)->name,
                    'type' => $ticket->type == Ticket::TYPE_DONOR ? Ticket::TYPE_DONOR : Ticket::TYPE_STAFF,
                    'first_name' => optional($ticket->user)->first_name,
                    'last_name' => optional($ticket->user)->last_name,
                    'group_id' => $ticket->group_id,
                    'group_name' => optional($ticket->group)->name,
                    'seat_num' => $ticket->seat_num,
                ];
            })];
        return Response::success($data);
    }

    public function store(Request $request): JsonResponse|JsonResource
    {
        $this->checkStore($request);
        $activity = $this->activityService->create($request->all());
        return Response::success([
            'id' => $activity->id,
            'status' => $activity->status,
        ]);
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        if ($activity->is_visible) {
            return Response::success(new ActivityResource($activity));
        } else {
            return Response::success($activity->cache);
        }
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function details(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $data = $activity->cache->toArray();
        $data['basic']['status'] = $activity->status;
        $data['basic']['state'] = $activity->state;
        return Response::success($data);
    }


    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        abort_if($activity->status == Activity::STATUS_REVIEW, 422, 'During review, please do not submit again');
        $this->checkUpdate($request);
        $activity->update(['cache' => $request->all()]);
        return Response::success([
            'id' => $activity->id,
            'status' => $activity->status,
        ]);
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $this->activityService->delete($activity);
        return Response::success();
    }

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function submit(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        Gate::authorize('check-charity-source', $activity);
        $this->checkSubmit($request);
        abort_if($activity->status == Activity::STATUS_REVIEW, 422, 'During review, please do not submit again');
        $activity->status = Activity::STATUS_REVIEW;
        $activity->cache = $request->all();
        $activity->save();
        return Response::success();
    }

    public function albumsIndex(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'sort' => 'sometimes|string|in:ASC,DESC',
            'page' => 'sometimes|numeric|min:1|not_in:0',
            'per_page' => 'sometimes|numeric|min:1|not_in:0',
        ]);
        $data = $activity->albums()->filter($request->all())
            ->select(['id', 'path'])->paginate($request->input('per_page', 15));
        return Response::success($data);
    }

    public function albumStore(Request $request, Activity $activity): JsonResponse|JsonResource
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'sometimes|url',
        ]);
        $models = [];
        foreach ($request->get('paths') as $item) {
            $models[] = new Album(['path' => $item, 'user_id' => Auth::id()]);
        }
        $activity->albums()->saveMany($models);
        return Response::success();
    }

    public function albumsDelete(Activity $activity, Album $album): JsonResponse|JsonResource
    {
        $activity->albums()->where(['id' => $album->id])->delete();
        return Response::success();
    }

    private function checkStore(Request $request): void
    {
        $request->validate([
            'basic.name' => 'required|string',
            'basic.description' => 'required|string',
            'basic.content' => 'nullable|string',
            'basic.location' => 'required|string',
            'basic.begin_time' => 'required|date|date_format:Y-m-d H:i:s',
            'basic.end_time' => 'required|date|date_format:Y-m-d H:i:s|after:basic.begin_time',
            'basic.price' => 'required|numeric|min:0',
            'basic.stock' => 'required|integer|min:1|not_in:0',
            'basic.is_private' => 'required|boolean',
            'basic.is_albums' => 'required|boolean',
            'basic.is_verification' => 'required|boolean',
            'basic.images' => 'required|array',
            'basic.specialty' => 'sometimes|array',
            'basic.specialty.*.title' => 'required|string',
            'basic.specialty.*.description' => 'required|string',
            'basic.timeline' => 'sometimes|array',
            'basic.timeline.*.time' => 'required|date',
            'basic.timeline.*.title' => 'required|string',
            'basic.timeline.*.description' => 'required|string',
            'lotteries' => 'sometimes|array',
            'lotteries.*.name' => 'required|string',
            'lotteries.*.description' => 'required|string',
            'lotteries.*.begin_time' => 'nullable|date|date_format:Y-m-d H:i:s',
            'lotteries.*.end_time' => 'nullable|date|date_format:Y-m-d H:i:s|after:lotteries.*.begin_time',
            'lotteries.*.standard_amount' => 'required|numeric|min:0',
            'lotteries.*.standard_oli_register' => 'nullable|boolean',
            'lotteries.*.type' => 'required|in:AUTOMATIC,MANUAL',
            'lotteries.*.draw_time' => 'exclude_unless:type,true|required|date_format:Y-m-d H:i:s',
            'lotteries.*.images' => 'required|array',
            'lotteries.*.images.*' => 'required|url',
            'lotteries.*.prizes' => 'sometimes|array',
            'lotteries.*.prizes.*.name' => 'required|string',
            'lotteries.*.prizes.*.description' => 'required|string',
            'lotteries.*.prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'lotteries.*.prizes.*.price' => 'required|numeric|min:0',
            'lotteries.*.prizes.*.content' => 'sometimes|string',
            'lotteries.*.prizes.*.images' => 'required|array',
            'lotteries.*.prizes.*.images.*' => 'required|url',
            'lotteries.*.prizes.*.sponsor' => 'sometimes',
            'lotteries.*.prizes.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales' => 'sometimes|array',
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'required|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0|not_in:0',
            'sales.*.content' => 'nullable|string',
            'sales.*.sponsor' => 'sometimes',
            'sales.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'auctions' => 'sometimes|array',
            'auctions.*.name' => 'required|string',
            'auctions.*.description' => 'nullable|string',
            'auctions.*.keyword' => 'nullable|array',
            'auctions.*.thumb' => 'nullable|string',
            'auctions.*.content' => 'nullable|string',
            'auctions.*.trait' => 'nullable|array',
            'auctions.*.images' => 'required|array',
            'auctions.*.price' => 'required|numeric|min:0|not_in:0',
            'auctions.*.start_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.end_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.is_online' => 'required|boolean',
            'auctions.*.sponsor' => 'sometimes',
            'auctions.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts' => 'sometimes|array',
            'gifts.*.name' => 'required|string',
            'gifts.*.description' => 'required|string',
            'gifts.*.content' => 'nullable|string',
            'gifts.*.sponsor' => 'sometimes',
            'gifts.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts.*.images' => 'required|array',
            'gifts.*.images.*' => 'required|url',
            'staffs' => 'sometimes|array',
            'staffs.*.type' => 'required|in:HOST,STAFF',
            'staffs.*.uid' => 'required|distinct|integer|exists:charity_user,user_id',
        ]);
    }

    private function checkUpdate(Request $request): void
    {
        $request->validate([
            'basic.id' => 'required|integer|exists:activities,id',
            'basic.name' => 'required|string',
            'basic.description' => 'required|string',
            'basic.content' => 'nullable|string',
            'basic.location' => 'required|string',
            'basic.begin_time' => 'required|date|date_format:Y-m-d H:i:s',
            'basic.end_time' => 'required|date|date_format:Y-m-d H:i:s|after:basic.begin_time',
            'basic.price' => 'required|numeric|min:0',
            'basic.stock' => 'required|integer|min:1|not_in:0',
            'basic.is_private' => 'required|boolean',
            'basic.is_albums' => 'required|boolean',
            'basic.is_verification' => 'required|boolean',
            'basic.images' => 'required|array',
            'basic.specialty' => 'sometimes|array',
            'basic.specialty.*.title' => 'required|string',
            'basic.specialty.*.description' => 'required|string',
            'basic.timeline' => 'sometimes|array',
            'basic.timeline.*.time' => 'required|date',
            'basic.timeline.*.title' => 'required|string',
            'basic.timeline.*.description' => 'required|string',
            'lotteries' => 'sometimes|array',
            'lotteries.*.id' => 'sometimes|integer|exists:lotteries,id',
            'lotteries.*.name' => 'required|string',
            'lotteries.*.description' => 'required|string',
            'lotteries.*.begin_time' => 'nullable|date|date_format:Y-m-d H:i:s',
            'lotteries.*.end_time' => 'nullable|date|date_format:Y-m-d H:i:s|after:lotteries.*.begin_time',
            'lotteries.*.standard_amount' => 'required|numeric|min:0',
            'lotteries.*.standard_oli_register' => 'nullable|boolean',
            'lotteries.*.type' => 'required|in:AUTOMATIC,MANUAL',
            'lotteries.*.draw_time' => 'exclude_unless:type,true|required|date_format:Y-m-d H:i:s',
            'lotteries.*.images' => 'required|array',
            'lotteries.*.images.*' => 'required|url',
            'lotteries.*.prizes' => 'sometimes|array',
            'lotteries.*.prizes.*.id' => 'sometimes|integer|exists:prizes,id',
            'lotteries.*.prizes.*.name' => 'required|string',
            'lotteries.*.prizes.*.description' => 'required|string',
            'lotteries.*.prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'lotteries.*.prizes.*.price' => 'required|numeric|min:0',
            'lotteries.*.prizes.*.content' => 'sometimes|string',
            'lotteries.*.prizes.*.images' => 'required|array',
            'lotteries.*.prizes.*.images.*' => 'required|url',
            'lotteries.*.prizes.*.sponsor' => 'sometimes',
            'lotteries.*.prizes.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales' => 'sometimes|array',
            'sales.*.id' => 'sometimes|integer|exists:goods,id',
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'required|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0',
            'sales.*.content' => 'nullable|string',
            'sales.*.sponsor' => 'sometimes',
            'sales.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'auctions' => 'sometimes|array',
            'auctions.*.id' => 'sometimes|integer|exists:auctions,id',
            'auctions.*.name' => 'required|string',
            'auctions.*.description' => 'nullable|string',
            'auctions.*.keyword' => 'nullable|array',
            'auctions.*.thumb' => 'nullable|string',
            'auctions.*.content' => 'nullable|string',
            'auctions.*.trait' => 'nullable|array',
            'auctions.*.images' => 'required|array',
            'auctions.*.price' => 'required|numeric|min:0|not_in:0',
            'auctions.*.start_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.end_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.is_online' => 'required|boolean',
            'auctions.*.sponsor' => 'sometimes',
            'auctions.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts' => 'sometimes|array',
            'gifts.*.name' => 'required|string',
            'gifts.*.description' => 'required|string',
            'gifts.*.content' => 'nullable|string',
            'gifts.*.sponsor' => 'sometimes',
            'gifts.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts.*.images' => 'required|array',
            'gifts.*.images.*' => 'required|url',
            'staffs' => 'sometimes|array',
            'staffs.*.id' => 'sometimes|integer|exists:tickets,id',
            'staffs.*.type' => 'required|in:HOST,STAFF',
            'staffs.*.uid' => 'required|distinct|integer|exists:charity_user,user_id',
        ]);
    }

    private function checkSubmit(Request $request): void
    {
        $request->validate([
            'basic.id' => 'required|integer|exists:activities,id',
            'basic.name' => 'required|string',
            'basic.description' => 'required|string',
            'basic.content' => 'nullable|string',
            'basic.location' => 'required|string',
            'basic.begin_time' => 'required|date|date_format:Y-m-d H:i:s',
            'basic.end_time' => 'required|date|date_format:Y-m-d H:i:s|after:basic.begin_time',
            'basic.price' => 'required|numeric|min:0',
            'basic.stock' => 'required|integer|min:1|not_in:0',
            'basic.is_private' => 'required|boolean',
            'basic.is_albums' => 'required|boolean',
            'basic.is_verification' => 'required|boolean',
            'basic.images' => 'required|array',
            'basic.specialty' => 'sometimes|array',
            'basic.specialty.*.title' => 'required|string',
            'basic.specialty.*.description' => 'required|string',
            'basic.timeline' => 'sometimes|array',
            'basic.timeline.*.time' => 'required|date',
            'basic.timeline.*.title' => 'required|string',
            'basic.timeline.*.description' => 'required|string',
            'lotteries' => 'sometimes|array',
            'lotteries.*.id' => 'sometimes|integer|exists:lotteries,id',
            'lotteries.*.name' => 'required|string',
            'lotteries.*.description' => 'required|string',
            'lotteries.*.begin_time' => 'nullable|date|date_format:Y-m-d H:i:s',
            'lotteries.*.end_time' => 'nullable|date|date_format:Y-m-d H:i:s|after:lotteries.*.begin_time',
            'lotteries.*.standard_oli_register' => 'nullable|boolean',
            'lotteries.*.standard_amount' => 'required|numeric|min:0',
            'lotteries.*.type' => 'required|in:AUTOMATIC,MANUAL',
            'lotteries.*.draw_time' => 'exclude_unless:type,true|required|date_format:Y-m-d H:i:s',
            'lotteries.*.images' => 'required|array',
            'lotteries.*.images.*' => 'required|url',
            'lotteries.*.prizes' => 'sometimes|array',
            'lotteries.*.prizes.*.id' => 'sometimes|integer|exists:prizes,id',
            'lotteries.*.prizes.*.name' => 'required|string',
            'lotteries.*.prizes.*.description' => 'required|string',
            'lotteries.*.prizes.*.stock' => 'required|integer|min:1|not_in:0',
            'lotteries.*.prizes.*.price' => 'required|numeric|min:0',
            'lotteries.*.prizes.*.content' => 'nullable|string',
            'lotteries.*.prizes.*.images' => 'required|array',
            'lotteries.*.prizes.*.images.*' => 'required|url',
            'lotteries.*.prizes.*.sponsor' => 'sometimes',
            'lotteries.*.prizes.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales' => 'sometimes|array',
            'sales.*.id' => 'sometimes|integer|exists:goods,id',
            'sales.*.name' => 'required|string',
            'sales.*.description' => 'required|string',
            'sales.*.stock' => 'required|integer|min:1|not_in:0',
            'sales.*.price' => 'required|numeric|min:0',
            'sales.*.content' => 'sometimes|string',
            'sales.*.sponsor' => 'sometimes',
            'sales.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'sales.*.images' => 'required|array',
            'sales.*.images.*' => 'required|url',
            'auctions' => 'sometimes|array',
            'auctions.*.id' => 'sometimes|integer|exists:auctions,id',
            'auctions.*.name' => 'required|string',
            'auctions.*.description' => 'nullable|string',
            'auctions.*.keyword' => 'nullable|array',
            'auctions.*.thumb' => 'nullable|string',
            'auctions.*.content' => 'nullable|string',
            'auctions.*.trait' => 'nullable|array',
            'auctions.*.images' => 'required|array',
            'auctions.*.price' => 'required|numeric|min:0|not_in:0',
            'auctions.*.start_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.end_time' => 'required|date|date_format:Y-m-d H:i:s',
            'auctions.*.is_online' => 'required|boolean',
            'auctions.*.sponsor' => 'sometimes',
            'auctions.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts' => 'sometimes|array',
            'gifts.*.name' => 'required|string',
            'gifts.*.description' => 'required|string',
            'gifts.*.content' => 'nullable|string',
            'gifts.*.sponsor' => 'sometimes',
            'gifts.*.sponsor.id' => 'sometimes|required|integer|exists:sponsors,id',
            'gifts.*.images' => 'required|array',
            'gifts.*.images.*' => 'required|url',
            'staffs' => 'required|array',
            'staffs.*.id' => 'sometimes|integer|exists:tickets,id',
            'staffs.*.type' => 'required|in:HOST,STAFF',
            'staffs.*.uid' => 'required|distinct|integer|exists:charity_user,user_id',
        ]);
    }

    private function getDetails(Activity $activity): array
    {
        $orders = $activity->orders()->filter(['payment_status' => Order::STATUS_PAID,])
            ->selectRaw('type, sum(amount) as total_amount')
            ->groupBy('type')->get();
        return [
            'lotteries' => $activity->lotteries->transform(function (Lottery $lottery) {
                return [
                    'id' => $lottery->id,
                    'type' => $lottery->draw_time ? Lottery::TYPE_AUTOMATIC : Lottery::TYPE_MANUAL,
                    'image' => collect($lottery->images)->first(),
                    'name' => $lottery->name,
                    'draw_time' => $lottery->draw_time,
                    'status' => $lottery->status,
                    'prizes' => $lottery->prizes->transform(function (Prize $prize) {
                        return [
                            'id' => $prize->id,
                            'name' => $prize->name,
                            'stock' => $prize->num,
                            'price' => floatval($prize->price),
                            'sponsor' => optional($prize->prizeable)->getMorphClass() != Sponsor::class ? [] : [
                                'id' => $prize->prizeable->id,
                                'name' => $prize->prizeable->name,
                                'logo' => $prize->prizeable->logo,
                            ],
                            'images' => $prize->images,
                            'description' => $prize->description,
                            'winners' => $prize->winners,
                        ];
                    }),
                ];
            }),
            'sales' => $activity->goods->transform(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->name,
                    'stock' => $goods->stock,
                    'image' => collect($goods->images)->first(),
                    'sale_num' => $goods->extends['sale_num'],
                    'income' => $goods->extends['sale_income'],
                    'order' => $goods->orders()->where(['payment_status' => Order::STATUS_PAID])
                        ->with('user')->get()->transform(function ($item) {
                            return [
                                'id' => optional($item->user)->id,
                                'name' => optional($item->user)->name,
                                'avatar' => optional($item->user)->avatar,
                            ];
                        }),
                ];
            }),
            'auctions' => $activity->auctions->transform(function (Auction $auction) {
                return [
                    'id' => $auction->id,
                    'name' => $auction->name,
                    'description' => $auction->description,
                    'thumb' => $auction->thumb,
                    'keyword' => $auction->keyword,
                    'content' => $auction->content,
                    'trait' => $auction->trait,
                    'is_online' => $auction->is_online,
                    'images' => $auction->images,
                    'price' => $auction->price,
                    'start_time' => $auction->start_time,
                    'end_time' => $auction->end_time,
                    'order' => $auction->orders()->where(['payment_status' => Order::STATUS_PAID])
                        ->with('user')->get()->transform(function ($item) {
                            return [
                                'id' => optional($item->user)->id,
                                'name' => optional($item->user)->name,
                                'avatar' => optional($item->user)->avatar,
                            ];
                        }),
                ];
            }),
            'gifts' => $activity->gifts->transform(function (Gift $gift) {
                return [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'image' => collect($gift->images)->first(),
                    'like' => $gift->likers()->get()->transform(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'avatar' => $item->avatar,
                            'phone' => $item->phone,
                        ];
                    }),
                ];
            }),
            'statistics' => [
                'income' => floatval($orders->sum('total_amount')),
                'constitute' => $orders
            ],
        ];
    }
}
