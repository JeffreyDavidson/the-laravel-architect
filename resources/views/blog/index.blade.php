@extends('layouts.app')

@section('title', 'Blog')

@push('head')
    @vite('resources/css/pages/listings-entry.css')
@endpush

@section('content')
<div class="blog-index bg-gray-50 dark:bg-[#0b1016]" data-blog-filter>
    <header class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016]">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
            <h1 id="blog-heading" class="max-w-4xl text-balance text-4xl font-semibold tracking-[-0.035em] text-gray-900 dark:text-gray-100 sm:text-5xl md:text-6xl">Notes from the work.</h1>
            <p class="mt-5 max-w-2xl text-pretty text-lg text-gray-600 dark:text-gray-400">Practical writing about Laravel architecture, testing, and the decisions behind maintainable applications.</p>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="blog-index__filters flex flex-col gap-5 border-b border-gray-200 py-6 dark:border-[#1e2a3a] lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-sm">
                <label for="blog-search" class="sr-only">Search posts</label>
                <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center">
                    <x-svg-icon name="search" class="h-4 w-4 text-gray-500" />
                </div>
                <input id="blog-search" type="search" data-blog-search placeholder="Search the archive"
                    class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-16 text-base text-gray-900 placeholder-gray-500 focus:border-brand-600 focus:outline-none focus:ring-1 focus:ring-brand-600 dark:border-brand-700 dark:bg-brand-950 dark:text-gray-100">
                <button hidden data-blog-clear type="button" aria-label="Clear search" class="absolute inset-y-0 right-3 rounded px-1 text-sm font-medium text-brand-600 hover:text-brand-500 focus-visible:outline-2 focus-visible:outline-brand-500">
                    Clear
                </button>
            </div>

            @if($categories->count())
                <nav aria-label="Filter articles by category" class="-mx-1 overflow-x-auto px-1">
                    <div class="flex min-w-max items-center gap-6">
                        <button type="button" data-blog-category="all" aria-pressed="true" class="category-pill active">
                            All <span>{{ $posts->count() }}</span>
                        </button>
                        @foreach($categories as $category)
                            <button type="button" data-blog-category="{{ $category->slug }}" aria-pressed="false" class="category-pill">
                                {{ $category->name }} <span>{{ $category->posts_count }}</span>
                            </button>
                        @endforeach
                    </div>
                </nav>
            @endif
        </div>

        <section aria-labelledby="blog-heading" class="py-10 md:py-14">
            <div class="blog-index__grid">
                @forelse($posts as $post)
                    <div data-blog-post data-category="{{ $post->category?->slug }}" data-search-content="{{ str($post->title . ' ' . ($post->excerpt ?? '') . ' ' . $post->tags->pluck('name')->join(' '))->lower() }}" class="blog-card-wrapper">
                        <x-blog-card :post="$post" editorial :priority="$loop->first" :showTags="false" />
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">No articles yet</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">New writing will appear here when it is published.</p>
                    </div>
                @endforelse

                <div hidden data-blog-empty class="col-span-full py-20 text-center">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">No matching articles</h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Nothing matched “<span data-blog-empty-query></span>”. Try another search or category.</p>
                    <button type="button" data-blog-reset class="mt-5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-base font-medium text-gray-800 hover:border-brand-500 hover:text-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:border-brand-700 dark:bg-brand-950 dark:text-gray-100">
                        Show all articles
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
