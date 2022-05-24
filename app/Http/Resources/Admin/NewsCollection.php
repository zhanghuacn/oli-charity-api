<?php

namespace App\Http\Resources\Admin;

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
                'banner' => $item->banner,
                'thumb' => $item->thumb,
                'keyword' => $item->keyword,
                'source' => $item->source,
                'description' => $item->description,
                'content' => $item->content,
                'status' => $item->status,
                'published_at' => $item->published_at,
                'sort' => $item->sort,
            ];
        });
    }
}
