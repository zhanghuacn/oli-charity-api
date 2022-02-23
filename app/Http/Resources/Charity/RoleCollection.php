<?php

namespace App\Http\Resources\Charity;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class RoleCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'permissions' => $item->permissions->pluck('name'),
                'created_at' => Carbon::parse($item->created_at)->tz(config('app.timezone'))->format('Y-m-d H:i:s'),
            ];
        });
    }
}
