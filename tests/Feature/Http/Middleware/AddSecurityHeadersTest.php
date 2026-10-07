<?php

use App\Models\NewsletterIssue;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;

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

    return "base-uri 'self'; connect-src 'self' https://challenges.cloudflare.com{$viteConnectSources}; default-src 'self'; font-src 'self' data:; form-action 'self'; frame-ancestors 'self'; frame-src https://challenges.cloudflare.com https://www.youtube-nocookie.com https://share.transistor.fm; img-src 'self' data: blob: https:; media-src 'self' blob: https:; object-src 'none'; script-src 'self' {$scriptPolicy} https://challenges.cloudflare.com{$viteScriptSources}; style-src 'self' 'unsafe-inline'; worker-src 'self' blob:";
}

function requiredHeader(?string $value): string
{
    if ($value === null) {
        throw new RuntimeException('The response did not contain the expected header.');
    }

    return $value;
}

it('adds security headers to public responses', function () {
    $response = get(route('home'));
    $policy = requiredHeader($response->headers->get('Content-Security-Policy'));

    preg_match("/'nonce-([^']+)'/", $policy, $matches);
    $nonce = $matches[1] ?? null;

    expect($nonce)->toBeString()
        ->not->toBeEmpty()
        ->and($policy)
        ->not->toContain("'unsafe-inline'", "'unsafe-eval'");

    $response
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy($nonce))
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertSeeHtml('<script nonce="'.$nonce.'">')
        ->assertSeeHtml('<script nonce="'.$nonce.'" type="application/ld+json">');
});

it('adds transport security only to secure responses', function () {
    get('https://the-laravel-architect.test/privacy')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

    get('http://the-laravel-architect.test/privacy')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});

it('adds security headers to admin responses', function () {
    $loginUrl = Filament::getPanel('admin')->getLoginUrl();

    if ($loginUrl === null) {
        throw new RuntimeException('The admin login URL was not configured.');
    }

    get($loginUrl)
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy())
        ->assertSeeHtml('livewire-standard.js')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('allows the local Vite development server without weakening other environments', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $response = get(route('home'));
    $policy = requiredHeader($response->headers->get('Content-Security-Policy'));

    preg_match("/'nonce-([^']+)'/", $policy, $matches);
    $nonce = $matches[1] ?? null;

    expect($nonce)->toBeString()
        ->not->toBeEmpty();

    $response
        ->assertOk()
        ->assertHeader('Content-Security-Policy', expectedContentSecurityPolicy($nonce, withVite: true));
});

function draftPreviewIssue(): NewsletterIssue
{
    return NewsletterIssue::factory()->create();
}

/**
 * Send the request for one private or error response case.
 *
 * @return TestResponse<Response>
 */
function privateResponse(string $case): TestResponse
{
    return match ($case) {
        'admin login' => get(Filament::getPanel('admin')->getLoginUrl() ?? ''),
        'signed preview' => get(URL::signedRoute('preview.newsletter-issue', draftPreviewIssue())),
        'unsigned preview' => get(route('preview.newsletter-issue', draftPreviewIssue())),
        'missing page' => get('/this-page-does-not-exist'),
        'server error' => (function (): TestResponse {
            Route::get('/security-headers-server-error', fn () => throw new RuntimeException('Boom.'));

            return get('/security-headers-server-error');
        })(),
        'maintenance mode' => (function (): TestResponse {
            config([
                'app.maintenance.driver' => 'cache',
                'app.maintenance.store' => 'array',
            ]);
            app()
                ->maintenanceMode()
                ->activate([]);

            return get(route('home'));
        })(),
        default => throw new InvalidArgumentException("Unknown response case [{$case}]."),
    };
}

it('keeps private and error responses out of caches and search indexes', function (string $case, int $status) {
    $response = privateResponse($case);

    $response
        ->assertStatus($status)
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Content-Security-Policy')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
})->with([
    ['admin login', 200],
    ['signed preview', 200],
    ['unsigned preview', 403],
    ['missing page', 404],
    ['server error', 500],
    ['maintenance mode', 503],
]);

it('leaves public pages cacheable and indexable', function () {
    get(route('home'))
        ->assertOk()
        ->assertHeaderMissing('X-Robots-Tag')
        ->assertHeader('Cache-Control', 'no-cache, private');
});
