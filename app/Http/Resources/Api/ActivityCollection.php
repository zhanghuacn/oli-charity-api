<?php

namespace App\Http\Resources\Api;

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
                'id' => optional($item)->id,
                'name' => optional($item)->name,
                'description' => optional($item)->description,
                'image' => collect(optional($item)->images)->first(),
                'location' => optional($item)->location,
                'begin_time' => optional($item)->begin_time,
                'end_time' => optional($item)->end_time,
            ];
        });
    }
}
