<?php

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Architecture', 'Browser', 'Feature', 'Integration');
pest()->extend(PHPUnit\Framework\TestCase::class)->in('Unit');
