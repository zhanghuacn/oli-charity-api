<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'image' => is_array($item->images) ? array_shift($item->images) : null,
                'location' => $item->location,
                'begin_time' => $item->begin_time,
                'end_time' => $item->end_time,
                'current' => Carbon::now()->between($item->begin_time, $item->end_time),
            ];
        });
    }
}
