<?php

namespace App\Http\Resources\Api;

use App\Models\Staff;
use App\Models\Ticket;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        $staff_users = $this->staffs()->with('user')->get();
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
            'hosts' => new UserCollection(
                $this->tickets()->with('user')->where('type', Ticket::TYPE_STAFF)->get()
                    ->map(function ($staff) {
                        return $staff->user;
                    })
            ),
            'sponsor' => [],
            'invite' => [],
            'role' => '',
            'is_private' => $this->is_private,
            'apply_status' => '',
            'is_follow' => Auth::check() ? $this->isSubscribedBy(Auth::user()) : false,
        ];
    }
}
