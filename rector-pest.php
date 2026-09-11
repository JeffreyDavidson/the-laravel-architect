<?php

declare(strict_types=1);

use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withComposerBased(laravel: true)
    ->withSets([
        PestSetList::CODING_STYLE,
    ])
    ->withSkip([
        // Preserve exact empty-array and empty-string assertions.
        SimplifyToLiteralBooleanRector::class,
        // Preserve regular-file checks; toBeFile() also accepts directories.
        UseToBeFileRector::class,
    ]);
