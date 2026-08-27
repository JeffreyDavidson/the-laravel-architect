@extends('layouts.app')

@push('head')
    @vite('resources/css/prism.css')
@endpush

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
        {{-- Header --}}
        <header class="mb-10">
            @if($post->category)
            <a href="{{ route('blog.category', $post->category) }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide hover:underline">{{ $post->category->name }}</a>
            @endif
            <h1 class="mb-5 mt-3 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">{{ $post->title }}</h1>
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
        @inject('responsiveImages', 'App\Services\ResponsiveImageVariants')
        @php
            $featuredImageSrcset = $responsiveImages->srcset($post->featured_image_path);
        @endphp
        <div class="rounded-xl overflow-hidden mb-10 bg-gray-100 dark:bg-gray-800">
            <picture class="block">
                @if($featuredImageSrcset)
                <source
                    type="image/webp"
                    srcset="{{ $featuredImageSrcset }}"
                    sizes="(min-width: 896px) 896px, calc(100vw - 2rem)"
                >
                @endif
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" decoding="async" fetchpriority="high" class="w-full">
            </picture>
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
            <p class="mb-3 font-mono text-xs uppercase tracking-[0.18em] text-brand-600">From the archive</p>
            <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">Continue reading</h2>
            <div class="divide-y divide-gray-200 border-y border-gray-200 dark:divide-[#1e2a3a] dark:border-[#1e2a3a]">
                @foreach($relatedPosts as $related)
                <a href="{{ route('blog.show', $related) }}" class="group grid gap-3 py-5 transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400 dark:hover:bg-[#111820] sm:grid-cols-[10rem_minmax(0,1fr)] sm:px-3">
                    <div class="flex items-center gap-3 sm:block">
                        @if($related->category)
                        <span class="text-xs font-semibold uppercase tracking-wider text-brand-600">{{ $related->category->name }}</span>
                        @endif
                        <span class="text-xs text-gray-500 sm:mt-2 sm:block">{{ $related->reading_time }} min read</span>
                    </div>
                    <div>
                        <h3 class="font-semibold leading-snug text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white">{{ $related->title }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-gray-600 dark:text-gray-400">{{ $related->excerpt }}</p>
                        <div class="mt-3 text-xs text-gray-500">{{ $related->published_at->format('M d, Y') }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </article>
@endsection
