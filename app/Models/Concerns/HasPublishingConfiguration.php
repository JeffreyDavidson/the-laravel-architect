<?php

namespace App\Models\Concerns;

use App\Models\Attributes\PublishingStatus as PublishingStatusAttribute;

trait HasPublishingConfiguration
{
    private static ?PublishingStatusAttribute $publishingStatusConfiguration = null;

    protected static function publishingStatusConfiguration(): PublishingStatusAttribute
    {
        if (self::$publishingStatusConfiguration !== null) {
            return self::$publishingStatusConfiguration;
        }

        $attributes = (new \ReflectionClass(static::class))
            ->getAttributes(PublishingStatusAttribute::class);

        return self::$publishingStatusConfiguration = ($attributes[0] ?? null)?->newInstance()
            ?? new PublishingStatusAttribute;
    }
}
