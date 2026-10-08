<?php

use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Page-aware SEO for a listing of the given size, viewed at the given page with 10 items per page.
 */
function paginatedPageSeo(int $total, int $page): PaginatedPageSeo
{
    return PaginatedPageSeo::forCurrentPage(new LengthAwarePaginator([], $total, 10, $page));
}

it('keeps the listing metadata on the first page', function () {
    $page = paginatedPageSeo(total: 25, page: 1);

    expect($page->title('Newsletter Archive'))->toBe('Newsletter Archive')
        ->and($page->description('Practical notes.'))
        ->toBe('Practical notes.')
        ->and($page->description(null))
        ->toBeNull()
        ->and($page->url('newsletter.index'))
        ->toBe(route('newsletter.index'));
});

it('numbers the metadata and canonical URL of a later page', function () {
    $page = paginatedPageSeo(total: 25, page: 2);

    expect($page->title('Newsletter Archive'))->toBe('Newsletter Archive — Page 2')
        ->and($page->description('Practical notes.'))
        ->toBe('Practical notes. Page 2 of 3.')
        ->and($page->description(null))
        ->toBe('Page 2 of 3.')
        ->and($page->url('archive.index', ['type' => 'writing']))
        ->toBe(route('archive.index', ['type' => 'writing', 'page' => 2]));
});

it('keeps the first page of an empty listing in range', function () {
    $page = paginatedPageSeo(total: 0, page: 1);

    expect($page->isOutOfRange())->toBeFalse()
        ->and($page->title('Archive'))
        ->toBe('Archive');
});

it('reports whether the current page is past the last one', function (int $page, bool $outOfRange) {
    expect(paginatedPageSeo(total: 25, page: $page)->isOutOfRange())->toBe($outOfRange);
})->with([
    'last page' => [3, false],
    'past the last page' => [4, true],
]);
