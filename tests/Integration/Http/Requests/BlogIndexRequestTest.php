<?php

use App\Http\Requests\BlogIndexRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

covers(BlogIndexRequest::class);

pest()->use(RefreshDatabase::class);

/**
 * @param  array<mixed>  $query
 */
function validatedBlogIndexRequest(array $query): BlogIndexRequest
{
    $request = BlogIndexRequest::create('/blog', 'GET', $query);
    $request->setContainer(app())
        ->setRedirector(app('redirect'));

    $request->validateResolved();

    return $request;
}

it('accepts a trimmed search term and an existing category', function () {
    // Arrange
    Category::factory()->create(['slug' => 'architecture']);

    // Act
    $request = validatedBlogIndexRequest(['q' => '  queues ', 'category' => ' architecture ', 'page' => '3']);

    // Assert
    expect($request->validated())->toBe(['q' => 'queues', 'category' => 'architecture', 'page' => '3']);
});

it('treats a malformed blog filter as not found', function (array $query) {
    // Act
    validatedBlogIndexRequest($query);
})->throws(NotFoundHttpException::class)
    ->with([
        'unknown category' => [['category' => 'no-such-category']],
        'overlong term' => [['q' => str_repeat('a', 121)]],
        'category list' => [['category' => ['architecture']]],
        'page zero' => [['page' => '0']],
    ]);
