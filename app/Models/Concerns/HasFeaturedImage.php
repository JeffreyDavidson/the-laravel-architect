<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

trait HasFeaturedImage
{
    /** @return Attribute<?string, never> */
    protected function featuredImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $path = $this->getAttribute($this->featuredImagePathColumn());

            if (! is_string($path) || $path === '') {
                return null;
            }

            return Storage::disk('public')->url($path);
        });
    }

    protected function featuredImagePathColumn(): string
    {
        return 'featured_image_path';
    }
}
