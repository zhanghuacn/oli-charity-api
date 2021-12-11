<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
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
                'title' => $item->title,
                'description' => $item->description,
                'image' => collect($item->images)->first(),
                'location' => $item->location,
                'begin_time' => $item->begin_time,
                'end_time' => $item->end_time,
                'current' => Carbon::now()->between($item->begin_time, $item->end_time),
            ];
        });
    }
}
