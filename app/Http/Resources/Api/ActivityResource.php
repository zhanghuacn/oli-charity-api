<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'description' => $this->description,
            'location' => $this->location,
            'begin_time' => $this->begin_time,
            'ent_time' => $this->ent_time,
            'images' => $this->images,
            'specialty' => $this->specialty,
            'timeline' => $this->timeline,
            'charity' => $this->charity->only(['id', 'name', 'logo']),
            'price' => $this->tickets['price'],
            'is_private' => $this->is_private,
            'host' => [],
            'invite' => [],
            'role' => 'DONOR',
            'apply_status' => 'WAIT',
            'is_follow' => Auth::check() ? $this->isSubscribedBy(Auth::user()) : false,
        ];
    }
}
