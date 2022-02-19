<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class BazaarCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => optional($item->goods)->id,
                'name' => optional($item->goods)->name,
                'image' => collect(optional($item->goods)->images)->first(),
                'price' => $item->price,
                'description' => optional($item->goods)->description,
                'is_receive' => $item->is_receive,
                'created_at' => Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
}
