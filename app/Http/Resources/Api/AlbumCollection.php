<?php

namespace App\Http\Resources\Api;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use JsonSerializable;

class AlbumCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'path' => $item->path,
                'created_at' => Carbon::parse($item->created_at)->tz(config('app.timezone'))->format('Y-m-d H:i:s'),
                'is_delete' => Auth::id() == $item->user_id,
            ];
        });
    }
}
