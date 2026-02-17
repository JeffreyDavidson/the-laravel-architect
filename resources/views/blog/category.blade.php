@extends('layouts.app')

@section('title', $category->name)

@section('content')
<style>
    .blog-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .blog-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4); border-color: #4A7FBF33; }
</style>

{{-- Hero --}}
<div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a]">
    <div class="absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <a href="{{ route('blog.index') }}" class="text-sm text-[#4A7FBF] hover:text-[#5A8FD0] transition-colors mb-4 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All Posts
        </a>
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">{{ $category->name }}</h1>
        @if($category->description)
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">{{ $category->description }}</p>
        @endif
    </div>
</div>

{{-- Posts --}}
<div class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="space-y-6">
            @forelse($posts as $post)
            <a href="{{ route('blog.show', $post) }}" class="blog-card group block rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117] overflow-hidden">
                <div class="p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xs text-gray-500">{{ $post->published_at->format('M d, Y') }}</span>
                        <span class="text-gray-300 dark:text-gray-700">·</span>
                        <span class="text-xs text-gray-500">{{ $post->reading_time }} min read</span>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $post->title }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2">{{ $post->excerpt }}</p>
                </div>
            </a>
            @empty
            <div class="text-center py-20 text-gray-500">No posts in this category yet.</div>
            @endforelse

            @if($posts->hasPages())
            <div class="pt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
