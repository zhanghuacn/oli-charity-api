<?php

namespace App\Http\Resources\Charity;

use Illuminate\Http\Resources\Json\JsonResource;

class CharityResource extends JsonResource
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
            'cards' => $this->extends['cards'],
            'is_stipe_bind' => $this->hasStripeAccountId() && $this->hasCompletedOnboarding()
        ];
    }
}
