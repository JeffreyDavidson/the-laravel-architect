<?php

use App\Http\Requests\ArchiveIndexRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(ArchiveIndexRequest::class);

/**
 * @param  array<mixed>  $query
 */
function validatedArchiveIndexRequest(array $query): ArchiveIndexRequest
{
    $request = ArchiveIndexRequest::create('/archive', 'GET', $query);
    $request->setContainer(app())
        ->setRedirector(app('redirect'));

    $request->validateResolved();

    return $request;
}

it('accepts and trims the archive filters', function () {
    // Act
    $request = validatedArchiveIndexRequest(['type' => '  writing ', 'year' => '2025', 'page' => '2']);

    // Assert
    expect($request->validated())->toBe(['type' => 'writing', 'year' => '2025', 'page' => '2']);
});

it('treats a malformed archive filter as not found', function (array $query) {
    // Act
    validatedArchiveIndexRequest($query);
})->throws(NotFoundHttpException::class)
    ->with([
        'unknown type' => [['type' => 'podcast-season']],
        'type list' => [['type' => ['writing']]],
        'year before 2000' => [['year' => '1999']],
        'future year' => [['year' => (string) (now()->year + 1)]],
        'page zero' => [['page' => '0']],
    ]);
