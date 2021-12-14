<?php

namespace App\Traits;

use Illuminate\Support\Fluent;

trait HasImagesProperty
{
    public function setImagesAttribute(array $images)
    {
        $this->attributes['images'] = json_encode($images);
    }

    public function getImagesAttribute(): Fluent
    {
        return new Fluent($this->getImages());
    }

    public function getImages(): array
    {
        return \array_replace_recursive(\defined('static::DEFAULT_IMAGES') ? \constant('static::DEFAULT_IMAGES') : [], \json_decode($this->attributes['images'] ?? '{}', true) ?? []);
    }
}
