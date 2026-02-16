@extends('layouts.app')

@section('title', 'Blog')

@section('content')
<style>
    .noise-overlay { position: relative; }
    .noise-overlay::after {
        content: ''; position: absolute; inset: 0; opacity: 0.04; pointer-events: none; z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat; background-size: 256px 256px;
    }
    .dot-grid-bg { position: relative; }
    .dot-grid-bg::before {
        content: ''; position: absolute; inset: 0; opacity: 0.03; pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 24px 24px; z-index: 0;
    }
    .dot-grid-bg > * { position: relative; z-index: 1; }
    .blog-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
        border-color: #4A7FBF33;
    }
    .category-pill {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .category-pill:hover, .category-pill.active {
        background: #4A7FBF20;
        border-color: #4A7FBF;
        color: #4A7FBF;
    }
</style>

{{-- Hero --}}
<div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a]">
    <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
    <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-0 dark:opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <div class="flex items-center gap-3 mb-4">
            <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
                <span class="text-[#4A7FBF]">$</span>
                <span>php artisan blog:latest</span>
                <span class="animate-pulse text-[#4A7FBF]">▊</span>
            </div>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">Blog</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">Thoughts on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.</p>
    </div>
</div>

{{-- Content --}}
<div class="dot-grid-bg" x-data="{
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
                    class="w-full pl-10 pr-4 py-2 text-sm bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-[#1e2a3a] rounded-xl text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:border-[#4A7FBF]/50 focus:ring-1 focus:ring-[#4A7FBF]/20 transition-colors">
                <button x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Category filters --}}
            @if($categories->count())
            <div class="flex flex-wrap gap-2">
            <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'active' : ''"
                class="category-pill px-4 py-1.5 text-xs font-semibold rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400">
                All Posts <span class="text-gray-600 ml-1">{{ $posts->count() }}</span>
            </button>
            @foreach($categories as $category)
            <button @click="activeCategory = '{{ $category->slug }}'"
                :class="activeCategory === '{{ $category->slug }}' ? 'active' : ''"
                class="category-pill px-4 py-1.5 text-xs font-semibold rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400">
                {{ $category->name }} <span class="text-gray-600 ml-1">{{ $category->posts_count }}</span>
            </button>
            @endforeach
            </div>
            @endif
        </div>

        {{-- Posts --}}
        <div class="space-y-6">
            @forelse($posts as $post)
            <div x-show="isVisible({ slug: '{{ $post->slug }}', category: '{{ $post->category?->slug }}', text: '{{ strtolower(addslashes($post->title . ' ' . ($post->excerpt ?? '') . ' ' . $post->tags->pluck('name')->join(' '))) }}' })"
                x-transition:enter="transition ease-out duration-500 delay-[{{ $loop->index * 75 }}ms]"
                x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="blog-card group rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117] overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        @if($post->category)
                        <span class="text-xs font-semibold uppercase tracking-wider" style="color: #4A7FBF;">{{ $post->category->name }}</span>
                        <span class="text-gray-300 dark:text-gray-700">·</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        <span class="text-gray-300 dark:text-gray-700">·</span>
                        <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
                    </div>

                    <a href="{{ route('blog.show', $post) }}">
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $post->title }}</h2>
                    </a>

                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>

                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($post->tags as $tag)
                        <a href="{{ route('blog.tag', $tag) }}" class="px-2.5 py-1 text-xs rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-500 bg-gray-50 dark:bg-[#161b22] hover:border-[#4A7FBF]/50 hover:text-[#4A7FBF] transition-colors relative z-10">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-20">
                <div class="inline-block bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-[#1e2a3a] rounded-xl px-6 py-4 mb-6">
                    <div class="font-mono text-sm">
                        <p class="text-gray-500">$ php artisan blog:latest</p>
                        <p class="text-yellow-400 mt-1">No posts found. Check back soon!</p>
                    </div>
                </div>
            </div>
            @endforelse

            {{-- No search results --}}
            <div x-show="visibleCount === 0" x-cloak class="text-center py-16">
                <div class="inline-block bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-[#1e2a3a] rounded-xl px-6 py-4">
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
