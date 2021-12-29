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
    public function create(Request $request): void
    {
        DB::transaction(function () use ($request) {
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
            $activity->tickets()->saveMany(collect($request->input('staffs'))->map(function ($item) use ($activity) {
                return new Ticket([
                    'charity_id' => getPermissionsTeamId(),
                    'user_id' => $item['user_id'],
                    'type' => $item['type'],
                    'price' => $activity->price,
                ]);
            }));
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
            $activity->lotteries()->whereNotIn('id', collect($request->input('lotteries'))->pluck('id'))->delete();
            Lottery::upsert(
                collect($request->input('lotteries'))->transform(function ($item) use ($activity) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'images' => $item['images'],
                        'begin_time' => $item['begin_time'],
                        'end_time' => $item['end_time'],
                        'standard_amount' => $item['standard_amount'],
                        'draw_time' => $item['draw_time'],
                    ];
                })->toArray(),
                ['id', 'activity_id']
            );
            Prize::whereNotIn('id', collect($request->input('sales'))->pluck('id'))->delete();
            Prize::where('activity_id', '=', $activity->id)->upsert(
                collect($request->input('lotteries.*.prizes'))->transform(function ($item) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'num' => $item['stock'],
                        'price' => $item['price'],
                        'images' => $item['images'],
                        'prizeable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                        'prizeable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                    ];
                })->toArray(),
                ['id', 'activity_id']
            );
            $activity->goods()->whereNotIn('id', collect($request->input('sales'))->pluck('id'))->delete();
            Goods::upsert(
                collect($request->input('sales'))->transform(function ($item) use ($activity) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'content' => $item['content'],
                        'price' => $item['price'],
                        'stock' => $item['stock'],
                        'images' => $item['images'],
                        'goodsable_type' => empty($item['sponsor']) ? Charity::class : Sponsor::class,
                        'goodsable_id' => empty($item['sponsor']) ? getPermissionsTeamId() : $item['sponsor']['id'],
                    ];
                })->toArray(),
                ['id', 'activity_id']
            );
            $activity->tickets()->where(['activity_id' => $activity->id])->whereNotIn('user_id', collect($request->input('staffs'))
                ->pluck('user_id'))->delete();
            Ticket::upsert(
                collect($request->input('staffs'))->map(function ($item) use ($activity) {
                    return [
                        'activity_id' => $activity->id,
                        'user_id' => $item['user_id'],
                        'type' => $item['type'],
                        'price' => $activity->price,
                    ];
                })->toArray(),
                ['id', 'activity_id']
            );
        });
    }
}
