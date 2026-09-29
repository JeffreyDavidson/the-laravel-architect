<?php

use App\Support\Content\Archives\PublicContentImportGuard;

it('permits content replacement only on safe target environments', function (string $environment, string $url, bool $staging, bool $allowed) {
    app()->detectEnvironment(fn (): string => $environment);
    config()->set('app.url', $url);

    expect(app(PublicContentImportGuard::class)->allows($staging))->toBe($allowed);
})->with([
    'local' => ['local', 'http://the-laravel-architect.test', false, true],
    'production' => ['production', 'https://thelaravelarchitect.com', true, false],
    'production hostname with local environment' => ['local', 'https://www.thelaravelarchitect.com', true, false],
    'normalized production hostname' => ['local', 'https://THELARAVELARCHITECT.COM.', true, false],
    'staging without permission flag' => ['production', 'https://staging.thelaravelarchitect.com', false, false],
    'staging with permission flag' => ['production', 'https://staging.thelaravelarchitect.com', true, true],
    'unknown production host' => ['production', 'https://example.test', true, false],
]);
