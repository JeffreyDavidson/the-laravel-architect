<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * A collection page's name, URL and listed items. On a paginated page the offset is the
 * number of items on earlier pages, so item positions continue across pages.
 */
final readonly class CollectionListing
{
    /**
     * @param  list<array{name: string, url: string}>  $items
     */
    public function __construct(
        public string $name,
        public string $url,
        public array $items,
        public int $positionOffset = 0,
    ) {}

    /**
     * @template TItem
     *
     * @param  LengthAwarePaginator<int, TItem>  $paginator
     * @param  Closure(TItem): array{name: string, url: string}  $toItem
     */
    public static function paginated(string $name, string $url, LengthAwarePaginator $paginator, Closure $toItem): self
    {
        return new self(
            $name,
            $url,
            array_values(array_map($toItem, $paginator->items())),
            ($paginator->currentPage() - 1) * $paginator->perPage(),
        );
    }
}
