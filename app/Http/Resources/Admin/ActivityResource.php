<?php

namespace App\Http\Resources\Admin;

use App\Models\Gift;
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
                'name' => $this->name,
                'description' => $this->description,
                'content' => $this->content,
                'location' => $this->location,
                'begin_time' => $this->begin_time,
                'end_time' => $this->end_time,
                'price' => floatval($this->price),
                'stock' => $this->stocks,
                'is_private' => $this->is_private,
                'is_verification' => $this->is_verification,
                'is_albums' => $this->extends['is_albums'],
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
                    'standard_oli_register' => $lottery->extends['standard_oli_register'] ?? false,
                    'standard_amount' => floatval($lottery->standard_amount),
                    'type' => $lottery->draw_time ? Lottery::TYPE_AUTOMATIC : Lottery::TYPE_MANUAL,
                    'draw_time' => $lottery->draw_time,
                    'images' => $lottery->images,
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
                        ];
                    }),

                ];
            }),
            'sales' => $this->goods->transform(function (Goods $goods) {
                return [
                    'id' => $goods->id,
                    'name' => $goods->name,
                    'stock' => $goods->stock,
                    'price' => floatval($goods->price),
                    'sponsor' => optional($goods->goodsable)->getMorphClass() != Sponsor::class ? [] : [
                        'id' => $goods->goodsable->id,
                        'name' => $goods->goodsable->name,
                        'logo' => $goods->goodsable->logo,
                    ],
                    'images' => $goods->images,
                    'description' => $goods->description,
                    'content' => $goods->content,
                ];
            }),
            'gifts' => $this->gifts->transform(function (Gift $gift) {
                return [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'sponsor' => optional($gift->giftable)->getMorphClass() != Sponsor::class ? [] : [
                        'id' => $gift->giftable->id,
                        'name' => $gift->giftable->name,
                        'logo' => $gift->giftable->logo,
                    ],
                    'images' => $gift->images,
                    'description' => $gift->description,
                    'content' => $gift->content,
                ];
            }),
            'staffs' => $this->tickets()->with('user')->whereIn('type', [TICKET::TYPE_HOST, Ticket::TYPE_STAFF])->get()
                ->transform(function (Ticket $ticket) {
                    return [
                        'id' => $ticket->id,
                        'type' => $ticket->type,
                        'uid' => $ticket->user_id,
                        'avatar' => $ticket->user->avatar,
                        'name' => $ticket->user->name,
                    ];
                })
        ];
    }
}
