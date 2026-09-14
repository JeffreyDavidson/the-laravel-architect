<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function expectedContentSecurityPolicy(?string $scriptNonce = null, bool $withVite = false): string
{
    $viteScriptSources = $withVite
        ? ' http://localhost:* http://127.0.0.1:* https://localhost:* https://127.0.0.1:*'
        : '';
    $viteConnectSources = $withVite
        ? $viteScriptSources.' ws://localhost:* ws://127.0.0.1:* wss://localhost:* wss://127.0.0.1:*'
        : '';

    $scriptPolicy = $scriptNonce === null
        ? "'unsafe-inline' 'unsafe-eval'"
        : "'nonce-{$scriptNonce}'";

    return "base-uri 'self'; connect-src 'self' https://api.usefathom.com https://cdn.usefathom.com https://challenges.cloudflare.com{$viteConnectSources}; default-src 'self'; font-src 'self' data:; form-action 'self'; frame-ancestors 'self'; frame-src https://challenges.cloudflare.com https://www.youtube-nocookie.com https://open.spotify.com https://embed.podcasts.apple.com; img-src 'self' data: blob: https:; media-src 'self' blob: https:; object-src 'none'; script-src 'self' {$scriptPolicy} https://cdn.usefathom.com https://challenges.cloudflare.com{$viteScriptSources}; style-src 'self' 'unsafe-inline'; worker-src 'self' blob:";
}

function requiredHeader(?string $value): string
{
    if ($value === null) {
        throw new RuntimeException('The response did not contain the expected header.');
    }

    return $value;
}

it('adds security headers to public responses', function () {
    $response = $this->get(route('home'));
    $policy = requiredHeader($response->headers->get('Content-Security-Policy'));

    preg_match("/'nonce-([^']+)'/", $policy, $matches);
    $nonce = $matches[1] ?? null;

    expect($nonce)->toBeString()->not->toBeEmpty()
        ->and($policy)->not->toContain("'unsafe-inline'", "'unsafe-eval'");

    $response
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy($nonce))
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertSeeHtml('<script nonce="'.$nonce.'">')->assertSeeHtml('<script nonce="'.$nonce.'" type="application/ld+json">');
});

it('adds transport security only to secure responses', function () {
    $this->get('https://the-laravel-architect.test/privacy')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

    $this->get('http://the-laravel-architect.test/privacy')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});

it('adds security headers to admin responses', function () {
    $loginUrl = Filament::getPanel('admin')->getLoginUrl();

    if ($loginUrl === null) {
        throw new RuntimeException('The admin login URL was not configured.');
    }

    $this->get($loginUrl)
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy())
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('allows the local Vite development server without weakening other environments', function () {
    $this->app->detectEnvironment(fn (): string => 'local');

    $response = $this->get(route('home'));
    $policy = requiredHeader($response->headers->get('Content-Security-Policy'));

    preg_match("/'nonce-([^']+)'/", $policy, $matches);
    $nonce = $matches[1] ?? null;

    expect($nonce)->toBeString()->not->toBeEmpty();

    $response
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy($nonce, withVite: true));
});
