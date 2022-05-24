<?php

namespace App\Http\Resources\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use JsonSerializable;

class TransferCollection extends ResourceCollection
{
    public function toArray($request): array|Collection|JsonSerializable|Arrayable
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'amount' => floatval($item->amount),
                'voucher' => $item->voucher,
                'status' => $item->status,
                'remark' => $item->remark,
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });
    }
}
