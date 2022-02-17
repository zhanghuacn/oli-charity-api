<?php

namespace App\Http\Resources\Admin;

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
                'end_time' => $item->end_time,
                'status' => $item->status,
                'state' => $item->state,
                'applies_count' => $item->applies_count ?? 0,
                'tickets_count' => $item->tickets_count ?? 0,
                'is_private' => $item->is_private,
            ];
        });
    }
}
