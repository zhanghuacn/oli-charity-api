<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => $this->images,
            'description' => $this->description,
            'content' => $this->content,
            'price' => floatval($this->price),
            'stock' => $this->stock,
            'sold_out' => $this->stock <= 0,
            'sponsor' => [
                'id' => optional($this->goodsable)->id,
                'name' => optional($this->goodsable)->name,
                'logo' => optional($this->goodsable)->logo,
            ],
        ];
    }
}
