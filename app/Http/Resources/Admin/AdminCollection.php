<?php

namespace App\Http\Resources\Admin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class AdminCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'username' => $item->username,
                'email' => $item->email,
                'avatar' => $item->avatar,
                'last_ip' => $item->last_ip,
                'last_active_at' => $item->last_active_at,
                'frozen_at' => $item->frozen_at,
                'roles' => $item->roles->pluck('name'),
                'created_at' => $item->created_at,
            ];
        });
    }
}
