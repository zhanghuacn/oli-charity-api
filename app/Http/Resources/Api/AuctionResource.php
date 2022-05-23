<?php

namespace App\Http\Resources\Api;

use App\Models\Auction;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class AuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'thumb' => $this->thumb,
            'keyword' => $this->keyword,
            'content' => $this->content,
            'trait' => $this->trait,
            'images' => $this->images,
            'is_online' => $this->is_online,
            'description' => $this->description,
            'price' => $this->price,
            'bid_count' => $this->bidRecord()->count(),
            'current_bid_price' => $this->current_bid_price,
            'current_bid_user' => $this->current_bid_user_id ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar,
            ] : null,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'visits' => visits(Auction::find($this->id))->count(),
            'is_payment_method' => \Auth::check() && \Auth::user()->hasPaymentMethod(),
            'sponsor' => [
                'id' => optional($this->auctionable)->id,
                'name' => optional($this->auctionable)->name,
                'logo' => optional($this->auctionable)->logo,
            ],
        ];
    }
}
