<?php

namespace App\Http\Resources\Sponsor;

use App\Models\Gift;
use App\Models\Goods;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        $lotteries = $this->lotteries()->with('prizes')->whereHas('prizes', function ($query) {
            $query->whereHasMorph('prizeable', Sponsor::class, function (Builder $query) {
                $query->where('id', '=', getPermissionsTeamId());
            });
        })->get();
        return [
            'basic' => [
                'name' => $this->name,
                'description' => $this->description,
                'content' => $this->content,
                'location' => $this->location,
                'begin_time' => $this->begin_time,
                'end_time' => $this->end_time,
                'price' => floatval($this->price),
                'is_private' => $this->is_private,
                'images' => $this->images,
                'specialty' => $this->extends['specialty'],
                'timeline' => $this->extends['timeline'],
            ],
            'lotteries' => $lotteries->transform(function (Lottery $lottery) {
                return [
                    'id' => $lottery->id,
                    'name' => $lottery->name,
                    'description' => $lottery->description,
                    'begin_time' => $lottery->begin_time,
                    'end_time' => $lottery->end_time,
                    'standard_amount' => $lottery->standard_amount,
                    'type' => $lottery->draw_time ? Lottery::TYPE_AUTOMATIC : Lottery::TYPE_MANUAL,
                    'draw_time' => $lottery->draw_time,
                    'images' => $lottery->images,
                    'prizes' => $lottery->prizes()->whereHasMorph('prizeable', Sponsor::class, function (Builder $query) {
                        $query->where('id', '=', getPermissionsTeamId());
                    })->get()->transform(function (Prize $prize) {
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
                        ];
                    }),

                ];
            }),
            'sales' => Goods::whereHasMorph('goodsable', Sponsor::class, function (Builder $query) {
                $query->where('id', '=', getPermissionsTeamId());
            })->where(['activity_id' => $this->id])->get()->transform(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->name,
                    'stock' => $goods->stock,
                    'price' => floatval($goods->price),
                    'images' => $goods->images,
                    'description' => $goods->description,
                    'content' => $goods->content,
                ];
            }),
            'gifts' => Gift::whereHasMorph('giftable', Sponsor::class, function (Builder $query) {
                $query->where('id', '=', getPermissionsTeamId());
            })->where(['activity_id' => $this->id])->get()->transform(function (Gift $gift) {
                return [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'images' => $gift->images,
                    'description' => $gift->description,
                    'content' => $gift->content,
                ];
            }),
            'staffs' => $this->tickets()->with('user')->whereIn('type', [TICKET::TYPE_HOST, Ticket::TYPE_STAFF])->get()
                ->transform(function (Ticket $ticket) {
                    return [
                        'type' => $ticket->type,
                        'uid' => $ticket->id,
                        'avatar' => $ticket->user->avatar,
                        'name' => $ticket->user->name,
                    ];
                })
        ];
    }
}
