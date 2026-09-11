<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\PendingCommand;
use JMac\Testing\Integrations\PHPUnit\VerifiesDoubles;
use Pest\Browser\Api\AwaitableWebpage;

abstract class TestCase extends BaseTestCase
{
    use VerifiesDoubles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * Run a console command with the fluent test output assertions enabled.
     *
     * @param  array<string, mixed>  $parameters
     */
    protected function artisanCommand(string $command, array $parameters = []): PendingCommand
    {
        $pendingCommand = $this->artisan($command, $parameters);

        if (! $pendingCommand instanceof PendingCommand) {
            throw new \RuntimeException('Console output mocking must be enabled for fluent command assertions.');
        }

        return $pendingCommand;
    }

    protected function browserPage(string $url, string $device): AwaitableWebpage
    {
        $page = \visit($url)->on()->{$device}()->wait(0);

        if (! $page instanceof AwaitableWebpage) {
            throw new \RuntimeException('Expected a browser page after selecting a device.');
        }

        return $page;
    }
}
