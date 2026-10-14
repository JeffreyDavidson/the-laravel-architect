<?php

declare(strict_types=1);

use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withCache(cacheDirectory: __DIR__.'/storage/framework/cache/rector-pest')
    ->withPaths([
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withSets([
        PestSetList::CODING_STYLE,
    ])
    ->withComposerBased(laravel: true);
