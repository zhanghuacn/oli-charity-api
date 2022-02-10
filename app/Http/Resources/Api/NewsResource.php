<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->thumb,
            'banner' => $this->banner,
            'description' => $this->description,
            'content' => $this->content,
            'time' => $this->published_at,
            'visits' => $this->visits()->count(),
        ];
    }
}
