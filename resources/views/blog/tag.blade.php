@extends('layouts.app')

@section('title', "Tagged: {$tag->name}")

@push('head')
    @vite('resources/css/pages/listings-entry.css')
@endpush

@section('content')
{{-- Hero --}}
<header class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016]">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
        <a href="{{ route('blog.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-brand-600 transition-colors hover:text-brand-500">
            <x-svg-icon name="chevron-left" class="w-4 h-4" />
            All Posts
        </a>
        <p class="mb-4 font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Tagged note</p>
        <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">{{ $tag->name }}</h1>
    </div>
</header>

{{-- Posts --}}
<div class="bg-gray-50 dark:bg-[#0b1016]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="space-y-6">
            @forelse($posts as $post)
            <x-blog-card :post="$post" :showTags="false" />
            @empty
            <x-empty-state message="No posts with this tag yet." />
            @endforelse

            @if($posts->hasPages())
            <div class="pt-8">{{ $posts->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
