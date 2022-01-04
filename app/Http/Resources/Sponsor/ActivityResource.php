<?php

namespace App\Http\Resources\Sponsor;

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
        return [
            'basic' => [
                'name' => $this->name,
                'description' => $this->description,
                'content' => $this->content,
                'location' => $this->location,
                'begin_time' => $this->begin_time,
                'end_time' => $this->end_time,
                'price' => $this->price,
                'is_private' => $this->is_private,
                'images' => $this->images,
                'specialty' => $this->extends['specialty'],
                'timeline' => $this->extends['timeline'],
            ],
            'lotteries' => $this->whereHas('prizes', function (Builder $query) {
                $query->whereHasMorph('prizeable', Sponsor::class, function (Builder $query) {
                    $query->where('id', '=', getPermissionsTeamId());
                });
            })->transform(function (Lottery $lottery) {
                return [
                    'name' => $lottery->name,
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
                            'images' => $prize->images,
                            'description' => $prize->description,
                        ];
                    }),
                ];
            }),
            'sales' => $this->whereHas('goods', function (Builder $query) {
                $query->whereHasMorph('goodsable', Sponsor::class, function (Builder $query) {
                    $query->where('id', '=', getPermissionsTeamId());
                });
            })->transform(function (Goods $goods) {
                return [
                    'name' => $goods->name,
                    'stock' => $goods->name,
                    'price' => $goods->name,
                    'images' => $goods->images,
                    'description' => $goods->description,
                    'content' => $goods->content,
                ];
            }),
            'staffs' => $this->tickets()->with('user')->whereIn('type', [TICKET::TYPE_HOST, Ticket::TYPE_STAFF])->get()
                ->transform(function (Ticket $ticket) {
                    return [
                        'type' => $ticket->type,
                        'user_id' => $ticket->id,
                        'avatar' => $ticket->user->avatar,
                        'name' => $ticket->user->name,
                    ];
                })
        ];
    }
}
