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
                'thumb' => $item->thumb,
                'keyword' => $item->keyword,
                'content' => $item->content,
                'trait' => $item->trait,
                'is_online' => $item->is_online,
                'images' => $item->images,
                'description' => $item->description,
                'bid_count' => $item->bid_record_count,
                'current_bid_price' => $item->current_bid_price,
                'price' => $item->price,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'visits' => $item->visits->score,
                'sponsor' => [
                    'id' => optional($item->auctionable)->id,
                    'name' => optional($item->auctionable)->name,
                    'logo' => optional($item->auctionable)->logo,
                ],
            ];
        });
    }
}
