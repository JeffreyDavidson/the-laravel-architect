<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Page-aware SEO for a paginated public listing. Later pages get their own canonical URL and a
 * " — Page N" title and description, while the first page keeps the listing's plain metadata.
 */
final readonly class PaginatedPageSeo
{
    private function __construct(
        private int $currentPage,
        private int $lastPage,
    ) {}

    /**
     * Read the listing's current page. The caller checks isOutOfRange() and answers a page past
     * the last one with a 404, so an empty page is never served or indexed.
     *
     * @template TItem
     *
     * @param  LengthAwarePaginator<int, TItem>  $paginator
     */
    public static function forCurrentPage(LengthAwarePaginator $paginator): self
    {
        return new self($paginator->currentPage(), $paginator->lastPage());
    }

    /** Whether the current page is past the last page. An empty listing still has a first page. */
    public function isOutOfRange(): bool
    {
        return $this->currentPage > $this->lastPage;
    }

    /**
     * The canonical URL of the current page: the route itself on the first page, with the page number after it.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function url(string $routeName, array $parameters = []): string
    {
        return route($routeName, $this->onFirstPage()
            ? $parameters
            : [...$parameters, 'page' => $this->currentPage]);
    }

    public function title(string $title): string
    {
        if ($this->onFirstPage()) {
            return $title;
        }

        return "{$title} — Page {$this->currentPage}";
    }

    /**
     * @return ($description is null ? string|null : string)
     */
    public function description(?string $description): ?string
    {
        if ($this->onFirstPage()) {
            return $description;
        }

        return trim("{$description} Page {$this->currentPage} of {$this->lastPage}.");
    }

    private function onFirstPage(): bool
    {
        return $this->currentPage <= 1;
    }
}
