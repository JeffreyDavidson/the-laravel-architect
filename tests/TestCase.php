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
        $devices = \visit($url)->on();
        $page = match ($device) {
            'mobile' => $devices->mobile(),
            'desktop' => $devices->desktop(),
            default => throw new \InvalidArgumentException("Unsupported browser test device: {$device}"),
        };

        return $page->wait(0);
    }
}
