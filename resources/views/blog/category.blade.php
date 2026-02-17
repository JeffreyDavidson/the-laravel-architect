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
            <x-icon name="chevron-left" class="w-4 h-4" />
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
            <x-blog-card :post="$post" :showCategory="false" :showTags="false" />
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
