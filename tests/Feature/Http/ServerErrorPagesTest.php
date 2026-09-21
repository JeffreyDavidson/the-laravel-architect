<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Vite as ViteAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

beforeEach(function () {
    config([
        'app.debug' => false,
        'database.default' => 'unavailable',
    ]);
    Http::preventStrayRequests();
    Vite::swap((new ViteAssets)->useHotFile('/nonexistent/error-page.hot')->useBuildDirectory('missing-error-page-assets'));
});

it('renders a safe branded page without database access or compiled assets', function (Throwable $exception, int $status, string $heading) {
    $request = Request::create('/unavailable-page', 'GET', server: ['HTTP_ACCEPT' => 'text/html']);

    $response = app(ExceptionHandler::class)->render($request, $exception);

    TestResponse::fromBaseResponse($response)
        ->assertStatus($status)
        ->assertSeeText('The Laravel Architect')
        ->assertSeeText($heading)
        ->assertSeeText('Try again')
        ->assertSeeHtml('href="/"')
        ->assertSeeHtml('name="robots" content="noindex, nofollow"')
        ->assertDontSee('private-database-detail')
        ->assertDontSee('private-maintenance-detail')
        ->assertDontSee('SQLSTATE')
        ->assertDontSee('/build/');

    if ($status === 503) {
        expect($response->headers->get('Retry-After'))->toBe('120');
    }
})->with([
    'database failure' => [
        fn (): QueryException => new QueryException('unavailable', 'select private-database-detail', [], new PDOException('SQLSTATE connection failed')),
        500,
        'Something went wrong.',
    ],
    'temporary unavailability' => [
        fn (): ServiceUnavailableHttpException => new ServiceUnavailableHttpException(120, 'private-maintenance-detail'),
        503,
        'Taking a short break.',
    ],
]);

it('renders the maintenance template without an exception or request-specific data', function () {
    $html = view('errors.503')->render();

    expect($html)
        ->toContain('Taking a short break.', 'href=""', 'href="/"')
        ->not->toContain('<script', '/build/');
});
