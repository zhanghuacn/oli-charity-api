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
                'email' => $item->email,
                'first_name' => $item->first_name,
                'middle_name' => $item->middle_name,
                'last_name' => $item->last_name,
                'birthday' => $item->birthday,
                'avatar' => $item->avatar,
                'profile' => $item->profile,
            ];
        });
    }
}
