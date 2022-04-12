<?php

namespace App\Http\Resources\Sponsor;

use Illuminate\Http\Resources\Json\JsonResource;

class SponsorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'backdrop' => $this->backdrop,
            'website' => $this->website,
            'description' => $this->description,
            'introduce' => $this->introduce,
        ];
    }
}
