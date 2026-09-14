<?php

declare(strict_types=1);

use Pest\Rector\Rules\Pest2ToPest3\UsesToExtendRector;
use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;
use RectorLaravel\Rector\MethodCall\AssertSeeToAssertSeeHtmlRector;

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
        // Keep the established Pest and Laravel test conventions in place.
        UsesToExtendRector::class,
        AssertSeeToAssertSeeHtmlRector::class,
    ]);
