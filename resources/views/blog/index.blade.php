@extends('layouts.app')

@section('title', 'Blog')

@push('head')
    @vite('resources/css/pages/listings-entry.css')
@endpush

@section('content')
{{-- Hero --}}
<x-hero-section>
    <div class="grid gap-6 md:grid-cols-[8rem_1fr] md:gap-10">
        <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Writing / 01</p>
        <div>
            <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">Notes from the work.</h1>
            <p class="max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">Thoughts on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.</p>
        </div>
    </div>
</x-hero-section>

{{-- Content --}}
<div class="bg-gray-50 dark:bg-[#0b1016]" data-blog-filter>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-16 lg:px-8">

        {{-- Search + Category filters --}}
        <div class="mb-12 flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-center dark:border-[#1e2a3a]">
            {{-- Search --}}
            <div class="relative flex-shrink-0 sm:w-72">
                <label for="blog-search" class="sr-only">Search posts</label>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input id="blog-search" type="search" data-blog-search placeholder="Search posts..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 transition-colors placeholder-gray-500 focus:border-brand-600/50 focus:outline-none focus:ring-1 focus:ring-brand-600/20 dark:border-brand-700 dark:bg-brand-950 dark:text-white">
                <button hidden data-blog-clear type="button" aria-label="Clear search" class="absolute inset-y-0 right-3 flex items-center rounded p-1 text-gray-500 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-brand-400 dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Category filters --}}
            @if($categories->count())
            <div class="flex flex-wrap gap-2">
            <button type="button" data-blog-category="all" aria-pressed="true"
                class="category-pill active rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 dark:border-brand-700 dark:text-gray-400">
                All Posts <span class="text-gray-600 ml-1">{{ $posts->count() }}</span>
            </button>
            @foreach($categories as $category)
            <button type="button" data-blog-category="{{ $category->slug }}" aria-pressed="false"
                class="category-pill rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 dark:border-brand-700 dark:text-gray-400">
                {{ $category->name }} <span class="text-gray-600 ml-1">{{ $category->posts_count }}</span>
            </button>
            @endforeach
            </div>
            @endif
        </div>

        {{-- Posts --}}
        <div class="flex flex-col">
            @forelse($posts as $post)
            <div data-blog-post data-category="{{ $post->category?->slug }}" data-search-content="{{ str($post->title . ' ' . ($post->excerpt ?? '') . ' ' . $post->tags->pluck('name')->join(' '))->lower() }}" class="blog-card-wrapper">
              <div>
                <x-blog-card :post="$post" />
              </div>
            </div>
            @empty
            <div class="text-center py-20">
                <div class="mb-6 inline-block rounded-xl border border-gray-200 bg-white px-6 py-4 dark:border-brand-700 dark:bg-brand-950">
                    <div class="font-mono text-sm">
                        <p class="text-gray-500">$ php artisan blog:latest</p>
                        <p class="text-yellow-400 mt-1">No posts found. Check back soon!</p>
                    </div>
                </div>
            </div>
            @endforelse

            {{-- No search results --}}
            <div hidden data-blog-empty class="text-center py-16">
                <div class="inline-block rounded-xl border border-gray-200 bg-white px-6 py-4 dark:border-brand-700 dark:bg-brand-950">
                    <div class="font-mono text-sm">
                        <p class="text-gray-500">$ grep -r "<span data-blog-empty-query></span>" ./posts</p>
                        <p class="text-yellow-400 mt-1">No matching posts found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
