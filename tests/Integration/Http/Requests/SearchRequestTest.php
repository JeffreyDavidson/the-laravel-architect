<?php

use App\Http\Requests\SearchRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(SearchRequest::class);

/**
 * @param  array<mixed>  $query
 */
function validatedSearchRequest(array $query): SearchRequest
{
    $request = SearchRequest::create('/search', 'GET', $query);
    $request->setContainer(app())
        ->setRedirector(app('redirect'));

    $request->validateResolved();

    return $request;
}

it('accepts a trimmed search term and a content type', function () {
    // Act
    $request = validatedSearchRequest(['q' => '  service container ', 'type' => 'writing']);

    // Assert
    expect($request->validated())->toBe(['q' => 'service container', 'type' => 'writing']);
});

it('treats a malformed search as not found', function (array $query) {
    // Act
    validatedSearchRequest($query);
})->throws(NotFoundHttpException::class)
    ->with([
        'overlong term' => [['q' => str_repeat('a', 121)]],
        'term list' => [['q' => ['laravel']]],
        'unknown type' => [['type' => 'podcast-season']],
    ]);
