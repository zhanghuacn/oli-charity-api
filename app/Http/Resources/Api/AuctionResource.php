<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => $this->images,
            'description' => $this->description,
            'price' => $this->price,
            'bid_count' => $this->bidRecord()->count(),
            'current_bid_price' => $this->current_bid_price,
            'current_bid_user' => [
                'id' => $this->user->id,
                'name' => $this->user->username,
                'avatar' => $this->user->avatar,
            ],
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
