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
    }
    .category-pill:hover, .category-pill.active {
        background: #4A7FBF20;
        border-color: #4A7FBF;
        color: #4A7FBF;
    }
</style>

{{-- Hero --}}
<div class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
    <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
    <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <div class="flex items-center gap-3 mb-4">
            <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
                <span class="text-[#4A7FBF]">$</span>
                <span>php artisan blog:latest</span>
                <span class="animate-pulse text-[#4A7FBF]">▊</span>
            </div>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Blog</h1>
        <p class="text-gray-400 text-lg max-w-2xl">Thoughts on Laravel, PHP, architecture patterns, testing, and the craft of building modern web applications.</p>
    </div>
</div>

{{-- Content --}}
<div class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">

        {{-- Category filters --}}
        @if($categories->count())
        <div class="flex flex-wrap gap-2 mb-10">
            <a href="{{ route('blog.index') }}" class="category-pill px-4 py-1.5 text-xs font-semibold rounded-full border border-[#1e2a3a] text-gray-400 {{ !request()->is('blog/category/*') ? 'active' : '' }}">
                All Posts
            </a>
            @foreach($categories as $category)
            <a href="{{ route('blog.category', $category) }}" class="category-pill px-4 py-1.5 text-xs font-semibold rounded-full border border-[#1e2a3a] text-gray-400">
                {{ $category->name }} <span class="text-gray-600 ml-1">{{ $category->posts_count }}</span>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Posts --}}
        <div class="space-y-6">
            @forelse($posts as $post)
            <a href="{{ route('blog.show', $post) }}" class="blog-card group block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        @if($post->category)
                        <span class="text-xs font-semibold uppercase tracking-wider" style="color: #4A7FBF;">{{ $post->category->name }}</span>
                        <span class="text-gray-700">·</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        <span class="text-gray-700">·</span>
                        <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
                    </div>

                    <h2 class="text-xl md:text-2xl font-bold text-white mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $post->title }}</h2>

                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>

                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($post->tags as $tag)
                        <span class="px-2.5 py-1 text-xs rounded-full border border-[#1e2a3a] text-gray-500 bg-[#161b22]">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            </a>
            @empty
            <div class="text-center py-20">
                <div class="inline-block bg-[#0D1117] border border-[#1e2a3a] rounded-xl px-6 py-4 mb-6">
                    <div class="font-mono text-sm">
                        <p class="text-gray-500">$ php artisan blog:latest</p>
                        <p class="text-yellow-400 mt-1">No posts found. Check back soon!</p>
                    </div>
                </div>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($posts->hasPages())
            <div class="pt-8">
                {{ $posts->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
