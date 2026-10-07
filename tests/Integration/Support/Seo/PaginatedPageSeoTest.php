<?php

use App\Support\Seo\PaginatedPageSeo;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

it('serves the first page of an empty listing', function () {
    expect(paginatedPageSeo(total: 0, page: 1)->title('Archive'))->toBe('Archive');
});

it('rejects a page past the last one', function () {
    expect(fn (): PaginatedPageSeo => paginatedPageSeo(total: 25, page: 4))
        ->toThrow(NotFoundHttpException::class);
});
