<?php

namespace App\Http\Resources\Admin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class UserCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'username' => $item->username,
                'email' => $item->name,
                'first_name' => $item->name,
                'middle_name' => $item->name,
                'last_name' => $item->name,
                'birthday' => $item->birthday,
                'avatar' => $item->avatar,
                'profile' => $item->profile,
            ];
        });
    }
}
