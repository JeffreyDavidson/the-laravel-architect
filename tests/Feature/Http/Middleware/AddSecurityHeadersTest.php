<?php

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function expectedContentSecurityPolicy(bool $withVite = false): string
{
    $viteScriptSources = $withVite
        ? ' http://localhost:* http://127.0.0.1:* https://localhost:* https://127.0.0.1:*'
        : '';
    $viteConnectSources = $withVite
        ? $viteScriptSources.' ws://localhost:* ws://127.0.0.1:* wss://localhost:* wss://127.0.0.1:*'
        : '';

    return "base-uri 'self'; connect-src 'self' https://api.usefathom.com https://cdn.usefathom.com https://challenges.cloudflare.com{$viteConnectSources}; default-src 'self'; font-src 'self' data:; form-action 'self'; frame-ancestors 'self'; frame-src https://challenges.cloudflare.com https://www.youtube-nocookie.com; img-src 'self' data: blob: https:; media-src 'self' blob: https:; object-src 'none'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.usefathom.com https://challenges.cloudflare.com{$viteScriptSources}; style-src 'self' 'unsafe-inline'; worker-src 'self' blob:";
}

it('adds security headers to public responses', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy())
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
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
    $this->get(Filament::getPanel('admin')->getLoginUrl())
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy())
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('allows the local Vite development server without weakening other environments', function () {
    $this->app->detectEnvironment(fn (): string => 'local');

    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy(withVite: true));
});
