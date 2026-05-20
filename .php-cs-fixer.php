<?php

declare(strict_types=1);

use ErickSkrauch\PhpCsFixer\Fixers;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

return (new Config())
    ->registerCustomFixers(new Fixers())
    ->setFinder($finder)
    ->setRules([
        'ErickSkrauch/line_break_after_statements' => true,
    ]);
