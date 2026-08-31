<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use JMac\Testing\Integrations\PHPUnit\VerifiesDoubles;

abstract class TestCase extends BaseTestCase
{
    use VerifiesDoubles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
