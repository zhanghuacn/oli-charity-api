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
            'backdrop' => $this->backdrop,
            'events' => $this->tickets()->count(),
            'donations' => $this->orders()->count(),
            'members' => $this->followings()->count(),
            'is_follow' => $this->has_followed ?? false,
        ];
    }
}
