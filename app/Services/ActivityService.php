<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Charity;
use App\Models\Goods;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityService
{
    public function create(Request $request): Activity
    {
        return DB::transaction(function () use ($request) {
            $activity = Activity::create([
                'charity_id' => getPermissionsTeamId(),
                'name' => $request->input('basic.name'),
                'description' => $request->input('basic.description'),
                'content' => $request->input('basic.content'),
                'location' => $request->input('basic.location'),
                'begin_time' => $request->input('basic.begin_time'),
                'end_time' => $request->input('basic.end_time'),
                'price' => $request->input('basic.price'),
                'stocks' => $request->input('basic.stock'),
                'is_private' => $request->input('basic.is_private'),
                'images' => $request->input('basic.images'),
                'extends' => [
                    'specialty' => $request->input('basic.specialty'),
                    'timeline' => $request->input('basic.timeline'),
                ],
                'cache' => $request->all()
            ]);
            if ($request->has('lotteries')) {
                collect($request->input('lotteries'))->map(function ($item) use ($activity) {
                    Lottery::create([
                        'activity_id' => $activity->id,
                        'charity_id' => getPermissionsTeamId(),
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'images' => $item['images'],
                        'begin_time' => $item['begin_time'],
                        'end_time' => $item['end_time'],
                        'standard_amount' => $item['standard_amount'],
                        'draw_time' => $item['draw_time'],
                    ])->prizes()->saveMany(
                        collect($item['prizes'])->map(function ($value) use ($activity) {
                            return new Prize([
                                'activity_id' => $activity->id,
                                'charity_id' => getPermissionsTeamId(),
                                'name' => $value['name'],
                                'description' => $value['description'],
                                'num' => $value['stock'],
                                'price' => $value['price'],
                                'images' => $value['images'],
                                'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                'prizeable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                            ]);
                        })
                    );
                });
            }
            if ($request->has('sales')) {
                $activity->goods()->saveMany(collect($request->input('sales'))->map(function ($item) use ($activity) {
                    return new Goods([
                        'activity_id' => $activity->id,
                        'charity_id' => getPermissionsTeamId(),
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'content' => $item['content'],
                        'price' => $item['price'],
                        'stock' => $item['stock'],
                        'images' => $item['images'],
                        'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                        'goodsable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                    ]);
                }));
            }
            if ($request->has('staffs')) {
                $activity->tickets()->saveMany(collect($request->input('staffs'))->map(function ($item) use ($activity) {
                    return new Ticket([
                        'charity_id' => getPermissionsTeamId(),
                        'user_id' => $item['uid'],
                        'type' => $item['type'],
                        'price' => 0,
                    ]);
                }));
            }
            return $activity;
        });
    }

    public function update(Activity $activity, Request $request): void
    {
        DB::transaction(function () use ($activity, $request) {
            $activity->update([
                'name' => $request->input('basic.name'),
                'description' => $request->input('basic.description'),
                'content' => $request->input('basic.content'),
                'location' => $request->input('basic.location'),
                'begin_time' => $request->input('basic.begin_time'),
                'end_time' => $request->input('basic.end_time'),
                'price' => $request->input('basic.price'),
                'stocks' => $request->input('basic.stock'),
                'is_private' => $request->input('basic.is_private'),
                'images' => $request->input('basic.images'),
                'extends' => [
                    'specialty' => $request->input('basic.specialty'),
                    'timeline' => $request->input('basic.timeline'),
                ],
                'cache' => $request->all()
            ]);
            $lottery_ids = collect($request->input('lotteries'))->whereNotNull('id')->pluck('id');
            if (!empty($lottery_ids)) {
                $activity->lotteries()->whereNotIn('id', $lottery_ids)->delete();
            } else {
                $activity->lotteries()->delete();
            }
            collect($request->input('lotteries'))->each(function ($item) use ($activity) {
                $activity->lotteries()->updateOrCreate(
                    [
                        'id' => $item['id'] ?? null,
                    ],
                    [
                        'charity_id' => getPermissionsTeamId(),
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'images' => $item['images'],
                        'begin_time' => $item['begin_time'],
                        'end_time' => $item['end_time'],
                        'standard_amount' => $item['standard_amount'],
                        'draw_time' => $item['draw_time'],
                    ]
                );
            });
            $prize_ids = collect($request->input('lotteries.*.prizes'))->whereNotNull('id')->pluck('id');
            if (!empty($prize_ids)) {
                $activity->prizes()->whereNotIn('id', $prize_ids)->delete();
            } else {
                $activity->prizes()->delete();
            }
            if ($request->has('lotteries.*.prizes')) {
                collect($request->input('lotteries.*.prizes'))->each(function ($item) use ($activity) {
                    $activity->prizes()->updateOrCreate(
                        [
                            'id' => $item['id'] ?? null,],
                        [
                            'charity_id' => getPermissionsTeamId(),
                            'name' => $item['name'],
                            'description' => $item['description'],
                            'num' => $item['stock'],
                            'price' => $item['price'],
                            'images' => $item['images'],
                            'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                            'prizeable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                        ]
                    );
                });
            }
            $goods_ids = collect($request->input('sales'))->whereNotNull('id')->pluck('id');
            if (!empty($goods_ids)) {
                $activity->goods()->whereNotIn('id', $lottery_ids)->delete();
            } else {
                $activity->goods()->delete();
            }
            if ($request->has('sales')) {
                collect($request->input('sales'))->each(function ($item) use ($activity) {
                    $activity->goods()->updateOrCreate(
                        [
                            'id' => $item['id'] ?? null,
                        ],
                        [
                            'charity_id' => getPermissionsTeamId(),
                            'name' => $item['name'],
                            'description' => $item['description'],
                            'content' => $item['content'],
                            'price' => $item['price'],
                            'stock' => $item['stock'],
                            'images' => $item['images'],
                            'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                            'goodsable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                        ]
                    );
                });
            }
            $ticket_ids = collect($request->input('staffs'))->whereNotNull('id')->pluck('id');
            if (!empty($ticket_ids)) {
                $activity->tickets()->whereNotIn('id', $ticket_ids)->delete();
            } else {
                $activity->tickets()->delete();
            }
            if ($request->has('staffs')) {
                collect($request->input('staffs'))->each(function ($item) use ($activity) {
                    $activity->tickets()->updateOrCreate(
                        [
                            'id' => $item['id'] ?? null,
                        ],
                        [
                            'charity_id' => getPermissionsTeamId(),
                            'user_id' => $item['uid'],
                            'type' => $item['type'],
                            'price' => 0,
                        ]
                    );
                });
            }
        });
    }

    public function delete(Activity $activity): void
    {
        DB::transaction(function () use ($activity) {
            $activity->lotteries->each(function (Lottery $lottery) {
                $lottery->prizes()->delete();
                $lottery->delete();
            });
            $activity->goods()->delete();
            $activity->tickets()->delete();
            $activity->delete();
        });
    }
}
