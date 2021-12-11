<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'profile' => $this->profile,
            'events' => 0,
            'donations' => 0,
            'members' => 0,
            'is_follow' => $this->has_followed ?? false,
        ];
    }
}
