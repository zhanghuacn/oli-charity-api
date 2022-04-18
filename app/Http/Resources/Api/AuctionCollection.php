<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use JsonSerializable;

class AuctionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'images' => $item->images,
                'description' => $item->description,
                'bid_count' => $item->bid_record_count,
                'current_bid_price' => $item->current_bid_price,
                'price' => $item->price,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
            ];
        });
    }
}
