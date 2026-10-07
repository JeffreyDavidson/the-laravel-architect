<?php

use App\Models\Subscriber;
use App\Support\Newsletter\UnsubscribeUrlGenerator;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\post;

pest()->use(RefreshDatabase::class);

function activeSubscriber(): Subscriber
{
    return Subscriber::factory()->create();
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

    post($url, ['List-Unsubscribe' => 'One-Click'])
        ->assertNoContent();

    $subscriber->refresh();
    expect($subscriber->isActive())
        ->toBeFalse();
});

it('accepts a burst of one-click posts from one mail provider address', function () {
    $url = app(UnsubscribeUrlGenerator::class)
        ->for(activeSubscriber());

    $statuses = [];
    foreach (range(1, 30) as $attempt) {
        $response = post($url, ['List-Unsubscribe' => 'One-Click']);
        $statuses[] = $response->status();
    }

    expect(array_unique($statuses))
        ->toBe([204]);
});

it('still limits one-click posts from one address', function () {
    $url = app(UnsubscribeUrlGenerator::class)
        ->for(activeSubscriber());
    foreach (range(1, 120) as $attempt) {
        post($url, ['List-Unsubscribe' => 'One-Click']);
    }

    $response = post($url, ['List-Unsubscribe' => 'One-Click']);

    $response->assertTooManyRequests();
});

it('rejects unsigned one-click posts', function () {
    $subscriber = activeSubscriber();

    post(route('newsletter.unsubscribe.oneClick', $subscriber), ['List-Unsubscribe' => 'One-Click'])
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
