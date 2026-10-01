<?php

use App\Services\TurnstileVerifier;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

covers(TurnstileVerifier::class);

beforeEach(function (): void {
    config()->set([
        'services.turnstile.secret_key' => 'test-secret',
        'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'services.turnstile.allowed_hostnames' => ['TheLaravelArchitect.COM'],
    ]);
    Http::preventStrayRequests();
});

it('rejects a missing, blank, numeric or non-string token without contacting Turnstile', function (mixed $token) {
    Http::fake();
    $input = $token === null ? [] : ['cf-turnstile-response' => $token];
    $request = Request::create(route('contact.store'), 'POST', $input);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
    Http::assertNothingSent();
})->with([
    'missing' => [null],
    'empty string' => [''],
    'whitespace only' => ['   '],
    'number' => [123],
    'array' => [['unexpected']],
]);

it('rejects a missing, blank, numeric or non-string secret key without contacting Turnstile', function (mixed $secret) {
    Http::fake();
    config()->set('services.turnstile.secret_key', $secret);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
    Http::assertNothingSent();
})->with([
    'missing' => [null],
    'empty string' => [''],
    'whitespace only' => ['   '],
    'number' => [123],
    'array' => [['unexpected']],
]);

it('submits the form credentials and accepts a case insensitive allowed hostname', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'THELARAVELARCHITECT.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', [
        'cf-turnstile-response' => 'test-token',
    ], server: ['REMOTE_ADDR' => '203.0.113.10']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeTrue();
    Http::assertSentCount(1);
    Http::assertSent(fn (ClientRequest $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
        && $request->data() === [
            'secret' => 'test-secret',
            'response' => 'test-token',
            'remoteip' => '203.0.113.10',
        ]);
});

it('requires an OK response with a boolean success value', function (mixed $success, int $status) {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => $success,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ], $status),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
    Http::assertSentCount(1);
})->with([
    'integer success' => [1, 200],
    'string success' => ['true', 200],
    'null success' => [null, 200],
    'client error' => [true, 400],
    'server error' => [true, 503],
]);

it('rejects a malformed hostname', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => ['thelaravelarchitect.com'],
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
});

it('returns false when the verification connection fails', function () {
    Http::fake([
        'challenges.cloudflare.com/*' => Http::failedConnection(),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
});

it('fails without contacting Turnstile when the expected action is blank', function () {
    Http::fake();
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, '');

    expect($passes)
        ->toBeFalse();
    Http::assertNothingSent();
});

it('fails closed without contacting Turnstile when the verification URL is blank or missing', function (?string $endpoint) {
    Http::fake();
    config()->set('services.turnstile.siteverify_url', $endpoint);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
    Http::assertNothingSent();
})->with([
    'missing' => [null],
    'empty' => [''],
    'whitespace' => ['   '],
]);

it('compares hostnames ignoring case, spaces and a trailing dot', function () {
    config()->set('services.turnstile.allowed_hostnames', [' TheLaravelArchitect.COM. ']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com.',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeTrue();
});

it('requires the allowed hostnames to be a list of non-empty strings', function (mixed $allowed) {
    config()->set('services.turnstile.allowed_hostnames', $allowed);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeFalse();
})->with([
    'a string instead of a list' => ['thelaravelarchitect.com'],
    'null' => [null],
    'non-string entries' => [[123, null, '']],
]);

it('ignores non-string entries in the allowed hostnames', function () {
    config()->set('services.turnstile.allowed_hostnames', [123, 'thelaravelarchitect.com']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'contact-form',
            'hostname' => 'thelaravelarchitect.com',
        ]),
    ]);
    $request = Request::create(route('contact.store'), 'POST', ['cf-turnstile-response' => 'test-token']);

    $passes = app(TurnstileVerifier::class)->passes($request, 'contact-form');

    expect($passes)
        ->toBeTrue();
});
