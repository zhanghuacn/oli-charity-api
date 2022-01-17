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
                'name' => $item->user->name,
                'avatar' => $item->user->avatar,
                'profile' => $item->user->profile,
                'status' => $item->status,
                'remark' => $item->remark,
            ];
        });
    }
}
