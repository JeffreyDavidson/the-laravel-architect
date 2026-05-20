@extends('layouts.app')

@section('title', $category->name)

@section('content')
{{-- Hero --}}
<div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-brand-700">
    <div class="absolute left-1/4 top-1/3 h-[500px] w-[500px] rounded-full bg-brand-600 opacity-0 blur-[120px] dark:opacity-[0.06]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
        <a href="{{ route('blog.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-brand-600 transition-colors hover:text-brand-500">
            <x-svg-icon name="chevron-left" class="w-4 h-4" />
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
            <x-empty-state message="No posts in this category yet." />
            @endforelse

            @if($posts->hasPages())
            <div class="pt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
