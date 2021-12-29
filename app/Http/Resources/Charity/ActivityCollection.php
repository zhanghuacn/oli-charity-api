<?php

namespace App\Http\Resources\Charity;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class ActivityCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'image' => collect($item->images)->first(),
                'name' => $item->name,
                'description' => $item->description,
                'location' => $item->location,
                'begin_time' => $item->begin_time,
                'ent_time' => $item->end_time,
                'status' => $item->status,
                'applies_count' => $item->applies_count,
                'tickets_count' => $item->tickets_count,
                'is_private' => $item->is_private,
            ];
        });
    }
}
