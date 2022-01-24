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
use Throwable;

class ActivityService
{
    public function create(array $arr): Activity
    {
        try {
            return DB::transaction(function () use ($arr) {
                $activity = Activity::create([
                    'charity_id' => getPermissionsTeamId(),
                    'name' => $arr['basic']['name'],
                    'description' => $arr['basic']['description'],
                    'content' => $arr['basic']['content'],
                    'location' => $arr['basic']['location'],
                    'begin_time' => $arr['basic']['begin_time'],
                    'end_time' => $arr['basic']['end_time'],
                    'price' => $arr['basic']['price'],
                    'stocks' => $arr['basic']['stock'],
                    'is_private' => $arr['basic']['is_private'],
                    'images' => $arr['basic']['images'],
                    'extends' => [
                        'specialty' => $arr['basic']['specialty'] ?? [],
                        'timeline' => $arr['basic']['timeline'] ?? [],
                        'is_albums' => $arr['basic']['is_albums'],
                    ],
                    'cache' => $arr
                ]);
                if (!empty($arr['lotteries'])) {
                    collect($arr['lotteries'])->map(function ($item) use ($activity) {
                        Lottery::create([
                            'activity_id' => $activity->id,
                            'charity_id' => getPermissionsTeamId(),
                            'name' => $item['name'],
                            'description' => $item['description'] ?? '',
                            'images' => $item['images'] ?? [],
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
                                    'description' => $value['description'] ?? '',
                                    'num' => $value['stock'],
                                    'price' => $value['price'],
                                    'images' => $value['images'] ?? [],
                                    'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                    'prizeable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                                ]);
                            })
                        );
                    });
                }
                if (!empty($arr['sales'])) {
                    $activity->goods()->saveMany(collect($arr['sales'])->map(function ($item) use ($activity) {
                        return new Goods([
                            'activity_id' => $activity->id,
                            'charity_id' => getPermissionsTeamId(),
                            'name' => $item['name'],
                            'description' => $item['description'] ?? '',
                            'content' => $item['content'] ?? '',
                            'price' => $item['price'],
                            'stock' => $item['stock'],
                            'images' => $item['images'] ?? [],
                            'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                            'goodsable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                        ]);
                    }));
                }
                if (!empty($arr['staffs'])) {
                    $activity->tickets()->saveMany(collect($arr['staffs'])->map(function ($item) use ($activity) {
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
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function update(Activity $activity, array $arr): void
    {
        try {
            DB::transaction(function () use ($activity, $arr) {
                $activity->update([
                    'name' => $arr['basic']['name'],
                    'description' => $arr['basic']['description'],
                    'content' => $arr['basic']['content'],
                    'location' => $arr['basic']['location'],
                    'begin_time' => $arr['basic']['begin_time'],
                    'end_time' => $arr['basic']['end_time'],
                    'price' => $arr['basic']['price'],
                    'stocks' => $arr['basic']['stock'],
                    'is_private' => $arr['basic']['is_private'],
                    'images' => $arr['basic']['images'],
                    'extends' => [
                        'specialty' => $arr['basic']['specialty'] ?? [],
                        'timeline' => $arr['basic']['timeline'] ?? [],
                        'is_albums' => $arr['basic']['is_albums'],
                    ],
                    'cache' => $arr
                ]);
                if (!empty($arr['lotteries'])) {
                    $lottery_ids = collect($arr['lotteries'])->whereNotNull('id')->pluck('id');
                    if (!empty($lottery_ids)) {
                        $activity->lotteries()->whereNotIn('id', $lottery_ids)->delete();
                    } else {
                        $activity->lotteries()->delete();
                    }
                    collect($arr['lotteries'])->whereNotNull('name')->each(function ($item) use ($activity) {
                        $lottery = Lottery::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
                                'charity_id' => getPermissionsTeamId(),
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'images' => $item['images'],
                                'begin_time' => $item['begin_time'],
                                'end_time' => $item['end_time'],
                                'standard_amount' => $item['standard_amount'],
                                'draw_time' => $item['draw_time'],
                            ]
                        );
                        $prize_ids = collect($item['prizes'])->whereNotNull('id')->pluck('id');
                        if (!empty($prize_ids)) {
                            $lottery->prizes()->whereNotIn('id', $prize_ids)->delete();
                        } else {
                            $lottery->prizes()->delete();
                        }
                        if (!empty($item['prizes'])) {
                            collect($item['prizes'])->whereNotNull('name')->each(function ($item) use ($activity, $lottery) {
                                Prize::updateOrCreate(
                                    [
                                        'id' => $item['id'] ?? null,
                                        'activity_id' => $activity->id,
                                        'charity_id' => getPermissionsTeamId(),
                                        'lottery_id' => $lottery->id,
                                    ],
                                    [
                                        'name' => $item['name'],
                                        'description' => $item['description'] ?? '',
                                        'num' => $item['stock'],
                                        'price' => $item['price'],
                                        'images' => $item['images'],
                                        'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                        'prizeable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                                    ]
                                );
                            });
                        }
                    });
                }
                if (!empty($arr['sales'])) {
                    $goods_ids = collect($arr['sales'])->whereNotNull('id')->pluck('id');
                    if (!empty($goods_ids)) {
                        $activity->goods()->whereNotIn('id', $goods_ids)->delete();
                    } else {
                        $activity->goods()->delete();
                    }
                    collect($arr['sales'])->each(function ($item) use ($activity) {
                        Goods::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
                                'charity_id' => getPermissionsTeamId(),
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'content' => $item['content'] ?? '',
                                'price' => $item['price'],
                                'stock' => $item['stock'],
                                'images' => $item['images'],
                                'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                'goodsable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                            ]
                        );
                    });
                }
                if (!empty($arr['staffs'])) {
                    $ticket_ids = collect($arr['staffs'])->whereNotNull('id')->pluck('id');
                    if (!empty($ticket_ids)) {
                        $activity->tickets()->whereNotIn('id', $ticket_ids)->delete();
                    } else {
                        $activity->tickets()->delete();
                    }
                    collect($arr['staffs'])->each(function ($item) use ($activity) {
                        Ticket::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
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
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }

    public function delete(Activity $activity): void
    {
        try {
            DB::transaction(function () use ($activity) {
                $activity->lotteries()->delete();
                $activity->goods()->delete();
                $activity->tickets()->delete();
                $activity->delete();
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }
}
