<?php

use App\Http\Requests\ProjectIndexRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @param  array<mixed>  $query
 */
function validatedProjectIndexRequest(array $query): ProjectIndexRequest
{
    $request = ProjectIndexRequest::create('/projects', 'GET', $query);
    $request->setContainer(app())
        ->setRedirector(app('redirect'));

    $request->validateResolved();

    return $request;
}

it('normalises the project filters', function (array $query, ?string $technology, ?string $tag) {
    $request = validatedProjectIndexRequest($query);

    expect($request->technology())->toBe($technology)
        ->and($request->tag())
        ->toBe($tag);
})->with([
    'no filters' => [[], null, null],
    'both filters' => [['technology' => 'Laravel', 'tag' => 'architecture'], 'Laravel', 'architecture'],
    'padded filters' => [['technology' => '  Laravel ', 'tag' => ' architecture  '], 'Laravel', 'architecture'],
    'blank filters' => [['technology' => '', 'tag' => '   '], null, null],
]);

it('treats a malformed project filter as not found', function (array $query) {
    validatedProjectIndexRequest($query);
})->throws(NotFoundHttpException::class)
    ->with([
        'technology list' => [['technology' => ['Laravel']]],
        'tag list' => [['tag' => ['architecture']]],
        'overlong technology' => [['technology' => str_repeat('a', 121)]],
        'overlong tag' => [['tag' => str_repeat('a', 121)]],
    ]);
