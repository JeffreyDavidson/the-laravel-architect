<?php

use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Architecture', 'Browser', 'Feature', 'Integration');
pest()->extend(PHPUnit\Framework\TestCase::class)
    ->in('Unit');

// Allow cold browser initialization on CI without adding fixed delays to tests.
pest()->browser()
    ->timeout(10000);
