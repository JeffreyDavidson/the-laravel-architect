@extends('layouts.app')

@section('title', 'Blog')

@section('content')
{{-- Hero --}}
<x-hero-section>
    <x-terminal-prompt command="blog:latest" />
    <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">Blog</h1>
    <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">Thoughts on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.</p>
</x-hero-section>

{{-- Content --}}
<div class="dot-grid-bg bg-gray-50 dark:bg-transparent" x-data="{
    activeCategory: 'all',
    search: '',
    posts: [
        @foreach($posts as $post)
        { slug: '{{ $post->slug }}', category: '{{ $post->category?->slug }}', text: '{{ strtolower(addslashes($post->title . ' ' . ($post->excerpt ?? '') . ' ' . $post->tags->pluck('name')->join(' '))) }}' },
        @endforeach
    ],
    isVisible(post) {
        const catMatch = this.activeCategory === 'all' || this.activeCategory === post.category;
        const searchMatch = this.search === '' || post.text.includes(this.search.toLowerCase());
        return catMatch && searchMatch;
    },
    get visibleCount() {
        return this.posts.filter(p => this.isVisible(p)).length;
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Search + Category filters --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-10">
            {{-- Search --}}
            <div class="relative flex-shrink-0 sm:w-72">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="search" placeholder="Search posts..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 transition-colors placeholder-gray-500 focus:border-brand-600/50 focus:outline-none focus:ring-1 focus:ring-brand-600/20 dark:border-brand-700 dark:bg-brand-950 dark:text-white">
                <button x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Category filters --}}
            @if($categories->count())
            <div class="flex flex-wrap gap-2">
            <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'active' : ''"
                class="category-pill rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 dark:border-brand-700 dark:text-gray-400">
                All Posts <span class="text-gray-600 ml-1">{{ $posts->count() }}</span>
            </button>
            @foreach($categories as $category)
            <button @click="activeCategory = '{{ $category->slug }}'"
                :class="activeCategory === '{{ $category->slug }}' ? 'active' : ''"
                class="category-pill rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 dark:border-brand-700 dark:text-gray-400">
                {{ $category->name }} <span class="text-gray-600 ml-1">{{ $category->posts_count }}</span>
            </button>
            @endforeach
            </div>
            @endif
        </div>

        {{-- Posts --}}
        <div class="flex flex-col gap-6">
            @forelse($posts as $post)
            <div :class="isVisible({ slug: '{{ $post->slug }}', category: '{{ $post->category?->slug }}', text: '{{ strtolower(addslashes($post->title . ' ' . ($post->excerpt ?? '') . ' ' . $post->tags->pluck('name')->join(' '))) }}' }) ? 'blog-card-wrapper' : 'blog-card-wrapper collapsed'"
                class="blog-card-wrapper">
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
            <div x-show="visibleCount === 0" x-cloak class="text-center py-16">
                <div class="inline-block rounded-xl border border-gray-200 bg-white px-6 py-4 dark:border-brand-700 dark:bg-brand-950">
                    <div class="font-mono text-sm">
                        <p class="text-gray-500">$ grep -r "<span x-text="search || activeCategory"></span>" ./posts</p>
                        <p class="text-yellow-400 mt-1">No matching posts found.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
