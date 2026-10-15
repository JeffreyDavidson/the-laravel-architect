<?php

use App\Models\Subscriber;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use JeffreyDavidson\CreatorKit\Presenters\SubscriberPresenter;
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
    $url = SubscriberPresenter::from($subscriber)
        ->unsubscribeUrl();

    $response = post($url, ['List-Unsubscribe' => 'One-Click']);

    $response
        ->assertNoContent();

    $subscriber->refresh();
    expect($subscriber->isActive())
        ->toBeFalse();
});

it('binds the one-click subscriber parameter when the routes are cached', function () {
    // Arrange: cache the real routes to a private file and boot a fresh application from it, as
    // production does after `artisan optimize`. Routes loaded from the cache skip routes/web.php.
    $cachePath = sys_get_temp_dir().'/tla-routes-'.bin2hex(random_bytes(8)).'.php';
    $testApplication = app();
    putenv("APP_ROUTES_CACHE={$cachePath}");
    $_ENV['APP_ROUTES_CACHE'] = $cachePath;
    $_SERVER['APP_ROUTES_CACHE'] = $cachePath;

    try {
        Artisan::call('route:cache');
        $cachedApplication = require base_path('bootstrap/app.php');
        if (! $cachedApplication instanceof Application) {
            throw new RuntimeException('bootstrap/app.php did not return an application.');
        }

        $cachedApplication->make(ConsoleKernel::class)
            ->bootstrap();

        // Act
        $binding = $cachedApplication->make(Router::class)
            ->getBindingCallback('subscriber');

        // Assert
        expect($cachedApplication->routesAreCached())->toBeTrue()
            ->and($binding)
            ->not->toBeNull();
    } finally {
        putenv('APP_ROUTES_CACHE');
        unset($_ENV['APP_ROUTES_CACHE'], $_SERVER['APP_ROUTES_CACHE']);
        File::delete($cachePath);
        Container::setInstance($testApplication);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($testApplication);
    }
});

it('accepts a burst of one-click posts from one mail provider address', function () {
    $url = SubscriberPresenter::from(activeSubscriber())
        ->unsubscribeUrl();

    $statuses = [];
    foreach (range(1, 30) as $attempt) {
        $response = post($url, ['List-Unsubscribe' => 'One-Click']);
        $statuses[] = $response->status();
    }

    expect(array_unique($statuses))
        ->toBe([204]);
});

it('still limits one-click posts from one address', function () {
    $url = SubscriberPresenter::from(activeSubscriber())
        ->unsubscribeUrl();
    foreach (range(1, 120) as $attempt) {
        post($url, ['List-Unsubscribe' => 'One-Click']);
    }

    $response = post($url, ['List-Unsubscribe' => 'One-Click']);

    $response->assertTooManyRequests();
});

it('rejects unsigned one-click posts', function () {
    $subscriber = activeSubscriber();

    $response = post(route('newsletter.unsubscribe.oneClick', $subscriber), ['List-Unsubscribe' => 'One-Click']);

    $response
        ->assertForbidden();

    $subscriber->refresh();
    expect($subscriber->isActive())
        ->toBeTrue();
});

it('accepts one-click posts from mail providers without a forgery token', function (string $routeName, bool $checksForgery) {
    $router = app('router');
    $route = $router
        ->getRoutes()
        ->getByName($routeName);

    if ($route === null) {
        throw new RuntimeException("Route [{$routeName}] is not registered.");
    }

    expect(in_array(PreventRequestForgery::class, $router->gatherRouteMiddleware($route), true))
        ->toBe($checksForgery);
})->with([
    'one-click unsubscribe skips it' => ['newsletter.unsubscribe.oneClick', false],
    'the unsubscribe form keeps it' => ['newsletter.unsubscribe.store', true],
    'signing up keeps it' => ['newsletter.subscribe', true],
]);

it('keeps forgery protection on other newsletter posts', function () {
    $request = Request::create(route('newsletter.subscribe'), 'POST', ['email' => 'reader@example.com']);
    $request->setLaravelSession(app('session.store'));
    $next = fn (): Response => response()->noContent();

    expect(fn () => enforcedForgeryMiddleware()->handle($request, $next))
        ->toThrow(TokenMismatchException::class);
});
