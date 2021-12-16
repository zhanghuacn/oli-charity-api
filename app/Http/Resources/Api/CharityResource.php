<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CharityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logo' => $this->logo,
            'introduce' => $this->introduce,
            'events' => $this->activities->count(),
            'donations' => optional($this->setting)->donations ?? 0,
            'members' => $this->favoriters()->count(),
            'is_follow' => Auth::check() ? $this->hasBeenFavoritedBy(Auth::user()) : false,
        ];
    }
}
