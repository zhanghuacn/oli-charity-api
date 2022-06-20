<?php

namespace App\Http\Resources\Charity;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class ApplyCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user->id,
                'name' => $item->user->name,
                'email' => $item->user->email,
                'phone' => $item->user->phone,
                'first_name' => $item->user->first_name,
                'last_name' => $item->user->last_name,
                'birthday' => $item->user->birthday,
                'address' => $item->user->address,
                'avatar' => $item->user->avatar,
                'profile' => $item->user->profile,
                'status' => $item->status,
                'remark' => $item->remark,
            ];
        });
    }
}
