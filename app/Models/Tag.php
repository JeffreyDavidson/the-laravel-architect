<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    public function resolveRouteBinding($value, $field = null)
    {
        $locale = app()->getLocale();

        return static::query()
            ->where("slug->{$locale}", $value)
            ->firstOrFail();
    }
}
