@if ($paginator->hasPages())
    <nav aria-label="Blog pagination" class="flex flex-wrap items-center justify-center gap-3">
        @unless ($paginator->onFirstPage())
            <a
                href="{{ $paginator->previousPageUrl() }}"
                wire:click.prevent="previousPage"
                rel="prev"
                class="text-brand-600 dark:text-brand-300 rounded px-3 py-2 hover:underline focus-visible:outline-2"
            >
                Previous
            </a>
        @endunless

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2" aria-hidden="true">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span
                            aria-current="page"
                            aria-label="Page {{ $page }}"
                            class="bg-brand-600 rounded px-3 py-2 font-semibold text-white"
                        >{{ $page }}</span>
                    @else
                        <a
                            href="{{ $url }}"
                            wire:click.prevent="gotoPage({{ $page }})"
                            aria-label="Go to page {{ $page }}"
                            class="text-brand-600 dark:text-brand-300 rounded px-3 py-2 hover:underline focus-visible:outline-2"
                        >{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                wire:click.prevent="nextPage"
                rel="next"
                class="text-brand-600 dark:text-brand-300 rounded px-3 py-2 hover:underline focus-visible:outline-2"
            >
                Next
            </a>
        @endif
    </nav>
@endif
