<?php

namespace App\Http\Resources\Api;

use App\Notifications\ApplyPaid;
use App\Notifications\InvitePaid;
use App\Notifications\LotteryPaid;
use App\Notifications\RemindPaid;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class NotificationCollection extends ResourceCollection
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'type' => match ($item->type) {
                    InvitePaid::class => 'INVITE',
                    LotteryPaid::class, RemindPaid::class => 'LOTTERY',
                    ApplyPaid::class => 'APPLY',
                },
                'event_id' => $item->data['activity_id'] ?? '',
                'title' => $item->data['title'] ?? '',
                'content' => $item->data['content'] ?? '',
                'time' => $item->created_at,
            ];
        });
    }
}
