<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
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
                'bid_price' => $item->bid_price,
                'user' => [
                    'id' => $item->user->id,
                    'name' => $item->user->username,
                    'avatar' => $item->user->avatar,
                ],
                'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}
