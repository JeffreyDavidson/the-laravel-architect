<div data-blog-filter wire:loading.attr="aria-busy" aria-busy="false">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="blog-index__filters dark:border-surface-border dark:border-surface-border flex flex-col gap-5 border-b border-gray-200 py-6 lg:flex-row lg:items-center lg:justify-between">
            <form
                method="GET"
                action="{{ route('blog.index') }}"
                wire:submit.prevent="applySearch"
                role="search"
                class="relative w-full lg:max-w-sm"
            >
                <label for="blog-search" class="sr-only">Search posts</label>
                <button
                    type="submit"
                    aria-label="Search"
                    class="text-brand-600 hover:text-brand-500 focus-visible:outline-brand-500 absolute inset-y-0 left-1 flex w-10 items-center justify-center rounded-l-lg focus-visible:outline-2"
                >
                    <x-svg-icon name="search" class="h-4 w-4 text-gray-500" />
                </button>
                @if ($categorySlug !== null)
                    <input type="hidden" name="category" value="{{ $categorySlug }}" />
                @endif
                <input
                    id="blog-search"
                    type="search"
                    name="q"
                    wire:model.live.debounce.300ms="search"
                    value="{{ $query }}"
                    maxlength="120"
                    placeholder="Search the archive"
                    class="focus:border-brand-600 focus:ring-brand-600 dark:border-brand-700 dark:bg-brand-950 w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-16 pl-10 text-base text-gray-900 placeholder-gray-500 focus:ring-1 focus:outline-none dark:text-gray-100"
                />
                @if ($query !== '')
                    <a
                        href="{{ route('blog.index', array_filter(['category' => $categorySlug], fn ($value) => $value !== null)) }}"
                        wire:click.prevent="clearSearch"
                        data-blog-clear
                        aria-label="Clear search"
                        class="text-brand-600 hover:text-brand-500 focus-visible:outline-brand-500 absolute inset-y-0 right-3 flex items-center rounded px-1 text-sm font-medium focus-visible:outline-2"
                    >
                        Clear
                    </a>
                @endif
            </form>

            @if ($categories->isNotEmpty())
                <nav aria-label="Filter articles by category" class="-mx-1 overflow-x-auto px-1">
                    <div class="flex min-w-max items-center gap-6">
                        <x-blog.category-filter
                            href="{{ route('blog.index', array_filter(['q' => $query !== '' ? $query : null], fn ($value) => $value !== null)) }}"
                            wire:click.prevent="selectCategory"
                            :active="$categorySlug === null"
                            :count="$publishedPostCount"
                        >
                            All
                        </x-blog.category-filter>
                        @foreach ($categories as $category)
                            <x-blog.category-filter
                                href="{{ route('blog.index', array_filter(['q' => $query !== '' ? $query : null, 'category' => $category->slug], fn ($value) => $value !== null)) }}"
                                wire:click.prevent="selectCategory(@js($category->slug))"
                                :active="$categorySlug === $category->slug"
                                :count="$category->posts_count"
                            >
                                {{ $category->name }}
                            </x-blog.category-filter>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>

        <section aria-labelledby="blog-heading" class="py-10 md:py-14">
            <p class="mb-8 text-sm text-gray-600 dark:text-gray-400" aria-live="polite" role="status">
                <span wire:loading>Updating articles…</span>
                <span wire:loading.remove>
                    @if ($posts->total() > 0)
                        Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }}.
                    @elseif ($query !== '' || $categorySlug !== null)
                        No articles found.
                    @else
                        No articles are published yet.
                    @endif
                </span>
            </p>

            <div class="grid grid-cols-1 gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($posts as $post)
                    <div wire:key="blog-post-{{ $post->getKey() }}" data-blog-post class="flex min-w-0">
                        <x-blog-card :post="$post" editorial :priority="$loop->first" :showTags="false" />
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $query !== '' || $categorySlug !== null ? 'No matching articles' : 'No articles yet' }}
                        </h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            @if ($query !== '')
                                Nothing matched “{{ $query }}”. Try another search.
                            @elseif ($categorySlug !== null)
                                No posts in this category yet.
                            @else
                                New writing will appear here when it is published.
                            @endif
                        </p>
                        @if ($query !== '' || $categorySlug !== null)
                            <a
                                href="{{ route('blog.index') }}"
                                wire:click.prevent="clearFilters"
                                class="hover:border-brand-500 hover:text-brand-600 focus-visible:outline-brand-500 dark:border-brand-700 dark:bg-brand-950 mt-5 inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-base font-medium text-gray-800 focus-visible:outline-2 focus-visible:outline-offset-2 dark:text-gray-100"
                            >
                                Show all articles
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($posts->hasPages())
                <div class="pt-10">{{ $posts->links('pagination.blog') }}</div>
            @endif
        </section>
    </div>
</div>
