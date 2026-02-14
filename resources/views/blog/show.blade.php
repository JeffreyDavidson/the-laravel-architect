@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? $post->excerpt)

@push('head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
@endpush

@section('content')
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Header --}}
        <header class="mb-10">
            @if($post->category)
            <a href="{{ route('blog.category', $post->category) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide hover:underline">{{ $post->category->name }}</a>
            @endif
            <h1 class="text-3xl sm:text-4xl font-bold mt-2 mb-4">{{ $post->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>{{ $post->author->name ?? 'Jeffrey Davidson' }}</span>
                <span>·</span>
                <time>{{ $post->published_at->format('F d, Y') }}</time>
                <span>·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        {{-- Featured Image --}}
        @if($post->featured_image)
        <div class="rounded-xl overflow-hidden mb-10 bg-gray-100 dark:bg-gray-800">
            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full">
        </div>
        @endif

        {{-- Content --}}
        <div class="prose prose-invert prose-lg max-w-none prose-headings:text-white prose-a:text-brand-400 prose-strong:text-white prose-code:text-brand-300">
            <x-markdown>{!! $post->content !!}</x-markdown>
        </div>

        {{-- Tags --}}
        @if($post->tags->count())
        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-wrap gap-2">
                @foreach($post->tags as $tag)
                <a href="{{ route('blog.tag', $tag) }}" class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-sm rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Related Posts --}}
        @if($relatedPosts->count())
        <div class="mt-16 pt-10 border-t border-gray-200 dark:border-gray-800">
            <h2 class="text-xl font-bold mb-6">Related Posts</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related) }}" class="group block">
                    <h3 class="font-semibold group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $related->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $related->published_at->format('M d, Y') }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection
