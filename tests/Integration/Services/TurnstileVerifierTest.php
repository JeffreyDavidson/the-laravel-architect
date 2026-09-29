<?php

use App\Services\TurnstileVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

covers(TurnstileVerifier::class);

beforeEach(function (): void {
    config()->set([
        'services.turnstile.secret_key' => 'test-secret',
        'services.turnstile.siteverify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'services.turnstile.allowed_hostnames' => ['thelaravelarchitect.com'],
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
