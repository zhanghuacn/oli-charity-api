<?php

namespace App\Http\Resources\Api;

use App\Models\Apply;
use App\Models\Lottery;
use App\Notifications\InvitePaid;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class NotificationCollection extends ResourceCollection
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            $type = match ($item->notifiable->getMorphClass()) {
                InvitePaid::class => 'INVITE',
                Lottery::class => 'LOTTERY',
                Apply::class => 'APPLY',
            };
            return [
                'id' => $item->id,
                'title' => $item->data->title,
                'type' => $type,
                'content' => $item->data->content,
                'time' => $item->created_at,
            ];
        });
    }
}
