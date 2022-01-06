<?php

namespace App\Http\Resources\Charity;

use App\Models\Goods;
use App\Models\Lottery;
use App\Models\Prize;
use App\Models\Sponsor;
use App\Models\Ticket;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'basic' => [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'content' => $this->content,
                'location' => $this->location,
                'begin_time' => $this->begin_time,
                'end_time' => $this->end_time,
                'price' => $this->price,
                'stock' => $this->stocks,
                'is_private' => $this->is_private,
                'images' => $this->images,
                'specialty' => $this->extends['specialty'],
                'timeline' => $this->extends['timeline'],
                'status' => $this->status,
            ],
            'lotteries' => $this->lotteries->transform(function (Lottery $lottery) {
                return [
                    'id' => $lottery->id,
                    'name' => $lottery->name,
                    'description' => $lottery->description,
                    'begin_time' => $lottery->begin_time,
                    'end_time' => $lottery->end_time,
                    'standard_amount' => $lottery->standard_amount,
                    'type' => $lottery->draw_time ? 'MANUAL' : 'AUTOMATIC',
                    'draw_time' => $lottery->draw_time,
                    'images' => $lottery->images,
                    'prizes' => $lottery->prizes->transform(function (Prize $prize) {
                        return [
                            'id' => $prize->id,
                            'name' => $prize->name,
                            'stock' => $prize->num,
                            'price' => $prize->price,
                            'sponsor' => $prize->prizeable->getMorphClass() != Sponsor::class ? [] : [
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
            'sales' => $this->goods->transform(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->name,
                    'stock' => $goods->name,
                    'price' => $goods->name,
                    'sponsor' => $goods->goodsable->getMorphClass() != Sponsor::class ? [] : [
                        'id' => $goods->goodsable->id,
                        'name' => $goods->goodsable->name,
                        'logo' => $goods->goodsable->logo,
                    ],
                    'images' => $goods->images,
                    'description' => $goods->description,
                    'content' => $goods->content,
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
