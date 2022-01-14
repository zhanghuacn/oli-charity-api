<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Auth;
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
            'portfolio' => $this->settings['portfolio'],
            'records' => $this->settings['records'],
            'events' => $this->tickets()->count(),
            'donations' => $this->orders()->count(),
            'members' => $this->followers()->count(),
            'is_follow' => Auth::check() && Auth::user()->isFollowing(User::find($this->id)),
        ];
    }
}
