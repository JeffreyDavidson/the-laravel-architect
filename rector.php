<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/bootstrap/providers.php',
        __DIR__.'/config',
        __DIR__.'/database/factories',
        __DIR__.'/database/seeders',
        __DIR__.'/routes',
    ])
    ->withPhpSets()
    ->withComposerBased(laravel: true)
    ->withSkip([
        // Preserve Laravel's default configuration style in these framework-owned files.
        __DIR__.'/config/backup.php',
        __DIR__.'/config/database.php',
        __DIR__.'/config/services.php',
    ]);
