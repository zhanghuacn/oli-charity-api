<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Auction;
use App\Models\Charity;
use App\Models\Gift;
use App\Models\Goods;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Throwable;

class ActivityService
{
    public function create(array $arr): Activity
    {
        try {
            return DB::transaction(function () use ($arr) {
                return Activity::create([
                    'charity_id' => getPermissionsTeamId(),
                    'name' => $arr['basic']['name'],
                    'description' => $arr['basic']['description'],
//                    'content' => $arr['basic']['content'],
                    'location' => $arr['basic']['location'],
                    'begin_time' => $arr['basic']['begin_time'],
                    'end_time' => $arr['basic']['end_time'],
                    'price' => $arr['basic']['price'],
                    'stocks' => $arr['basic']['stock'],
                    'is_private' => $arr['basic']['is_private'],
                    'is_verification' => $arr['basic']['is_verification'],
                    'images' => $arr['basic']['images'],
                    'extends' => [
                        'specialty' => $arr['basic']['specialty'] ?? [],
                        'timeline' => $arr['basic']['timeline'] ?? [],
                        'is_albums' => $arr['basic']['is_albums'] ?? false,
                    ],
                    'cache' => $arr
                ]);
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
//                    'content' => $arr['basic']['content'],
                    'location' => $arr['basic']['location'],
                    'begin_time' => $arr['basic']['begin_time'],
                    'end_time' => $arr['basic']['end_time'],
                    'price' => $arr['basic']['price'],
                    'stocks' => $arr['basic']['stock'],
                    'is_private' => $arr['basic']['is_private'],
                    'is_verification' => $arr['basic']['is_verification'],
                    'images' => $arr['basic']['images'],
                    'extends' => [
                        'specialty' => $arr['basic']['specialty'] ?? [],
                        'timeline' => $arr['basic']['timeline'] ?? [],
                        'is_albums' => $arr['basic']['is_albums'] ?? false,
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
                                'charity_id' => $activity->charity_id,
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'images' => $item['images'],
                                'begin_time' => $item['begin_time'] ?? null,
                                'end_time' => $item['end_time'] ?? null,
                                'extends->standard_oli_register' => $item['standard_oli_register'] ?? false,
                                'standard_amount' => $item['standard_amount'],
                                'draw_time' => $item['draw_time'] ?? null,
                            ]
                        );
                        if (!empty($item['prizes'])) {
                            $prize_ids = collect($item['prizes'])->whereNotNull('id')->pluck('id');
                            if (!empty($prize_ids)) {
                                $lottery->prizes()->whereNotIn('id', $prize_ids)->delete();
                            } else {
                                $lottery->prizes()->delete();
                            }
                            collect($item['prizes'])->whereNotNull('name')->each(function ($item) use ($activity, $lottery) {
                                Prize::updateOrCreate(
                                    [
                                        'id' => $item['id'] ?? null,
                                        'activity_id' => $activity->id,
                                        'charity_id' => $activity->charity_id,
                                        'lottery_id' => $lottery->id,
                                    ],
                                    [
                                        'name' => $item['name'],
                                        'description' => $item['description'] ?? '',
                                        'num' => $item['stock'],
                                        'price' => $item['price'],
                                        'images' => $item['images'],
                                        'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                        'prizeable_id' => empty($item['sponsor']) ? $activity->charity_id : $item['sponsor']['id'],
                                    ]
                                );
                            });
                        }
                    });
                } else {
                    $activity->lotteries()->delete();
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
                                'charity_id' => $activity->charity_id,
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'content' => $item['content'] ?? '',
                                'price' => $item['price'],
                                'stock' => $item['stock'],
                                'images' => $item['images'],
                                'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                'goodsable_id' => empty($item['sponsor']) ? $activity->charity_id : $item['sponsor']['id'],
                            ]
                        );
                    });
                } else {
                    $activity->goods()->delete();
                }
                if (!empty($arr['auctions'])) {
                    $auction_ids = collect($arr['auctions'])->whereNotNull('id')->pluck('id');
                    if (!empty($auction_ids)) {
                        $activity->auctions()->whereNotIn('id', $auction_ids)->delete();
                    } else {
                        $activity->auctions()->delete();
                    }
                    collect($arr['auctions'])->each(function ($item) use ($activity) {
                        Auction::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
                                'charity_id' => $activity->charity_id,
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'thumb' => $item['thumb'],
                                'keyword' => $item['keyword'],
                                'content' => $item['content'],
                                'trait' => $item['trait'],
                                'images' => $item['images'],
                                'price' => $item['price'],
                                'is_online' => $item['is_online'],
                                'start_time' => $item['start_time'],
                                'end_time' => $item['end_time'],
                                'auctionable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                'auctionable_id' => empty($item['sponsor']) ? $activity->charity_id : $item['sponsor']['id'],
                            ]
                        );
                    });
                } else {
                    $activity->auctions()->delete();
                }
                if (!empty($arr['gifts'])) {
                    $gift_ids = collect($arr['gifts'])->whereNotNull('id')->pluck('id');
                    if (!empty($gift_ids)) {
                        $activity->gifts()->whereNotIn('id', $gift_ids)->delete();
                    } else {
                        $activity->gifts()->delete();
                    }
                    collect($arr['gifts'])->each(function ($item) use ($activity) {
                        Gift::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
                                'charity_id' => $activity->charity_id,
                            ],
                            [
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'content' => $item['content'] ?? '',
                                'images' => $item['images'],
                                'giftable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                                'giftable_id' => empty($item['sponsor']) ? $activity->charity_id : $item['sponsor']['id'],
                            ]
                        );
                    });
                } else {
                    $activity->gifts()->delete();
                }
                if (!empty($arr['staffs'])) {
                    $ticket_ids = collect($arr['staffs'])->whereNotNull('id')->pluck('id');
                    if (!empty($ticket_ids)) {
                        $activity->tickets()->whereIn('type', [Ticket::TYPE_HOST, Ticket::TYPE_STAFF])->whereNotIn('id', $ticket_ids)->delete();
                    } else {
                        $activity->tickets()->whereIn('type', [Ticket::TYPE_HOST, Ticket::TYPE_STAFF])->delete();
                    }
                    collect($arr['staffs'])->each(function ($item) use ($activity) {
                        Ticket::updateOrCreate(
                            [
                                'id' => $item['id'] ?? null,
                                'activity_id' => $activity->id,
                            ],
                            [
                                'charity_id' => $activity->charity_id,
                                'user_id' => $item['uid'],
                                'verified_at' => now(),
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
                $activity->gifts()->delete();
                $activity->delete();
            });
        } catch (Throwable $e) {
            abort(500, $e->getMessage());
        }
    }
}
