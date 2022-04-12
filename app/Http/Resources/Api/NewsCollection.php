<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class NewsCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'keyword' => $item->keyword,
                'banner' => $item->banner,
                'image' => $item->thumb,
                'description' => $item->description,
                'time' => $item->published_at
            ];
        });
    }
}
