<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class AuctionBidRecordCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'price' => $item->price,
                'bid_price' => $item->current_bid_price,
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->username,
                    'avatar' => $item->user->avatar,
                ],
                'created_at' => $item->created_at,
            ];
        });
    }
}
