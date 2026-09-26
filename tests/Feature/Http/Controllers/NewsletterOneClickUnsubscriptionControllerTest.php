<?php

use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

pest()->use(RefreshDatabase::class);

function activeSubscriber(): Subscriber
{
    return Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);
}

/**
 * The forgery middleware is bypassed while running tests, so this copy
 * enforces it to prove which paths are exempt.
 */
function enforcedForgeryMiddleware(): PreventRequestForgery
{
    return new class(app(), app(Encrypter::class)) extends PreventRequestForgery
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    };
}

it('unsubscribes with a signed one-click post', function () {
    $subscriber = activeSubscriber();
    $url = app(UnsubscribeUrlGenerator::class)
        ->for($subscriber);

    $this->post($url, ['List-Unsubscribe' => 'One-Click'])
        ->assertNoContent();

    $subscriber->refresh();
    expect($subscriber->isActive())
        ->toBeFalse();
});

it('rejects unsigned one-click posts', function () {
    $subscriber = activeSubscriber();

    $this->post(route('newsletter.unsubscribe.oneClick', $subscriber), ['List-Unsubscribe' => 'One-Click'])
        ->assertForbidden();

    $subscriber->refresh();
    expect($subscriber->isActive())
        ->toBeTrue();
});

it('accepts one-click posts from mail providers without a forgery token', function () {
    $url = app(UnsubscribeUrlGenerator::class)
        ->for(activeSubscriber());
    $request = Request::create($url, 'POST', ['List-Unsubscribe' => 'One-Click']);
    $request->setLaravelSession(app('session.store'));

    $next = fn (): Response => response()->noContent();

    $response = enforcedForgeryMiddleware()
        ->handle($request, $next);

    if (! $response instanceof Response) {
        throw new RuntimeException('The middleware must return an HTTP response.');
    }
    expect($response->getStatusCode())
        ->toBe(204);
});

it('keeps forgery protection on other newsletter posts', function () {
    $request = Request::create(route('newsletter.subscribe'), 'POST', ['email' => 'reader@example.com']);
    $request->setLaravelSession(app('session.store'));
    $next = fn (): Response => response()->noContent();

    expect(fn () => enforcedForgeryMiddleware()->handle($request, $next))
        ->toThrow(TokenMismatchException::class);
});
