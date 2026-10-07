<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\TracksActivity;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use TracksActivity;

    public static function bootHasSlug(): void
    {
        static::creating(function (SpatieTag $model): void {
            foreach ($model->getTranslatedLocales('name') as $locale) {
                if (! is_string($locale)) {
                    continue;
                }

                if (filled($model->getTranslation('slug', $locale, false))) {
                    continue;
                }

                $model->setTranslation('slug', $locale, $model->generateSlug($locale));
            }
        });
    }
}
