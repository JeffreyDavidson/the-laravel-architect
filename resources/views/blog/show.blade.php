@extends('layouts.app')

@push('head')
    @vite(['resources/css/prism.css', 'resources/js/prism.js'])
@endpush

@section('content')
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Header --}}
        <header class="mb-10">
            @if($post->category)
            <a href="{{ route('blog.category', $post->category) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide hover:underline">{{ $post->category->name }}</a>
            @endif
            <h1 class="text-3xl sm:text-4xl font-bold mt-2 mb-4 text-gray-900 dark:text-white">{{ $post->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>{{ $post->author->name ?? 'Jeffrey Davidson' }}</span>
                <span>·</span>
                <time>{{ $post->published_at->format('F d, Y') }}</time>
                <span>·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        {{-- Featured Image --}}
        @if($post->featured_image_url)
        <div class="rounded-xl overflow-hidden mb-10 bg-gray-100 dark:bg-gray-800">
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" decoding="async" fetchpriority="high" class="w-full">
        </div>
        @endif

        {{-- Content --}}
        <x-prose class="prose-a:text-brand-400 prose-code:text-brand-300">
            {!! Str::markdown(
                $post->content,
                ['heading_permalink' => ['insert' => 'none', 'apply_id_to_heading' => true, 'id_prefix' => '']],
                [new League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension],
            ) !!}
        </x-prose>

        {{-- Tags --}}
        @if($post->tags->count())
        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <x-tag-pill :tag="$tag" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- Related Posts --}}
        @if($relatedPosts->count())
        <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-700/50">
            <h2 class="text-xl font-bold mb-8 text-gray-900 dark:text-white">Continue Reading</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related) }}" class="group block bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 rounded-xl p-5 hover:border-blue-500/30 hover:bg-gray-100 dark:hover:bg-gray-800/80 transition-[color,border-color,background-color,box-shadow] duration-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400">
                    <div class="flex items-center gap-3 mb-3">
                        @if($related->category)
                        <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ $related->category->name }}</span>
                        @endif
                        <span class="text-xs text-gray-500">{{ $related->reading_time }} min read</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors leading-snug mb-2">{{ $related->title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $related->excerpt }}</p>
                    <div class="mt-3 text-xs text-gray-500">{{ $related->published_at->format('M d, Y') }}</div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection
