@extends('layouts.app')

@push('head')
    @vite(['resources/css/pages/article-entry.css', 'resources/css/prism.css'])
@endpush

@section('content')
    <div class="article-progress fixed left-0 top-0 z-[60] h-0.5 w-full bg-brand-500" data-article-progress aria-hidden="true"></div>

    <article class="article-page" data-article>
        <header class="mx-auto max-w-6xl px-4 pb-8 pt-12 sm:px-6 sm:pb-10 sm:pt-16 lg:px-8 lg:pt-20">
            @if($post->category)
            <a href="{{ route('blog.category', $post->category) }}" class="font-mono text-sm font-semibold uppercase tracking-wide text-brand-600 hover:underline dark:text-brand-300">{{ $post->category->name }}</a>
            @endif

            <h1 class="mt-4 max-w-[20ch] text-pretty text-4xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-5xl lg:text-6xl">{{ $post->title }}</h1>

            @if($post->excerpt)
            <p class="mt-6 max-w-[68ch] text-pretty text-lg text-gray-600 dark:text-gray-300 sm:text-xl">{{ $post->excerpt }}</p>
            @endif

            <div class="mt-7 flex flex-wrap items-center gap-x-3 gap-y-2 text-base text-gray-600 dark:text-gray-400 sm:text-sm">
                <a href="{{ route('about') }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-gray-200 dark:hover:text-brand-300">{{ $post->author->name ?? 'Jeffrey Davidson' }}</a>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F d, Y') }}</time>
                <span aria-hidden="true">·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-post-artwork
                :post="$post"
                sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
                :priority="true"
                class="aspect-[3/2] rounded-xl bg-gray-100 outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900 dark:outline-white/10"
            />
        </div>

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[13rem_minmax(0,70ch)] lg:gap-16 lg:px-8 lg:py-20">
            <aside class="hidden lg:block" aria-label="Article navigation">
                <nav class="sticky top-24" data-article-toc hidden>
                    <p class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">On this page</p>
                    <div class="max-h-[calc(100vh-9rem)] overflow-y-auto" data-article-toc-list></div>
                </nav>
            </aside>

            <div class="min-w-0">
                <details class="mb-10 rounded-xl bg-gray-50 p-4 outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900/60 dark:outline-white/10 lg:hidden" data-article-toc hidden>
                    <summary class="cursor-pointer text-base font-semibold text-gray-900 dark:text-white">On this page</summary>
                    <nav class="mt-3" aria-label="Article navigation" data-article-toc-list></nav>
                </details>

                <x-prose class="article-prose max-w-[70ch] prose-a:text-brand-600 dark:prose-a:text-brand-300 prose-code:text-brand-300">
                    {!! Str::markdown(
                        $post->content,
                        ['heading_permalink' => ['insert' => 'none', 'apply_id_to_heading' => true, 'id_prefix' => '']],
                        [new League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension],
                    ) !!}
                </x-prose>

                @if($post->tags->count())
                <div class="mt-12 border-t border-gray-200 pt-7 dark:border-gray-800">
                    <div class="flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                        <x-tag-pill :tag="$tag" />
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-12 grid gap-5 border-t border-gray-200 pt-8 dark:border-gray-800 sm:grid-cols-[4rem_minmax(0,1fr)] sm:items-start">
                    <img src="{{ Vite::asset('resources/images/avatar-320.webp') }}" alt="Jeffrey Davidson" width="64" height="64" loading="lazy" decoding="async" class="size-16 rounded-xl object-cover">
                    <div>
                        <p class="font-semibold text-gray-950 dark:text-white">Written by Jeffrey Davidson</p>
                        <p class="mt-2 text-pretty text-base text-gray-600 dark:text-gray-400">Laravel developer and software architect sharing practical lessons from building and maintaining real applications.</p>
                        <a href="{{ route('about') }}" class="mt-3 inline-flex text-sm font-semibold text-brand-600 hover:underline dark:text-brand-300">More about Jeffrey</a>
                    </div>
                </div>
            </div>
        </div>

        @if($relatedPosts->count())
        <section class="border-t border-gray-200 bg-gray-50 py-14 dark:border-brand-800/50 dark:bg-[#0b1016] sm:py-20" aria-labelledby="related-posts-heading">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <h2 id="related-posts-heading" class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-3xl">Continue reading</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @foreach($relatedPosts as $related)
                    <article class="group overflow-hidden rounded-xl bg-white outline-1 -outline-offset-1 outline-black/5 dark:bg-brand-900/60 dark:outline-white/10">
                        <a href="{{ route('blog.show', $related) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-500">
                            <x-post-artwork
                                :post="$related"
                                sizes="(min-width: 768px) 560px, calc(100vw - 2rem)"
                                class="aspect-[3/2] bg-gray-100 dark:bg-brand-900"
                            />
                            <div class="p-6">
                                @if($related->category)
                                <span class="font-mono text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-300">{{ $related->category->name }}</span>
                                @endif
                                <h3 class="mt-2 text-xl font-semibold tracking-tight text-gray-950 transition-colors group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-300">{{ $related->title }}</h3>
                                <p class="mt-3 line-clamp-2 text-pretty text-base text-gray-600 dark:text-gray-400">{{ $related->excerpt }}</p>
                                <div class="mt-5 text-sm text-gray-500">{{ $related->reading_time }} min read</div>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </article>
@endsection
