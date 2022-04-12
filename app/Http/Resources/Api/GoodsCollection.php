<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class GoodsCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => collect($item->images)->first(),
                'description' => $item->description,
                'sponsor' => [
                    'id' => optional($item->goodsable)->id,
                    'name' => optional($item->goodsable)->name,
                    'logo' => optional($item->goodsable)->logo,
                ],
            ];
        });
    }
}
