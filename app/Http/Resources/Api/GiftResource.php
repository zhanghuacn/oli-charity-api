<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GiftResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => $this->images,
            'description' => $this->description,
            'content' => $this->content,
            'is_like' => Auth::check() ? $this->isLikedBy(Auth::user()) : false,
            'sponsor' => [
                'id' => optional($this->giftable)->id,
                'name' => optional($this->giftable)->name,
                'logo' => optional($this->giftable)->logo,
            ],
            'like' => $this->likers()->get()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'avatar' => $item->avatar,
                ];
            })
        ];
    }
}
