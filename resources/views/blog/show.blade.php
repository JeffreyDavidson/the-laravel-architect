@extends('layouts.app')

@push('head')
    @vite(['resources/css/pages/article-entry.css', 'resources/css/prism.css'])
@endpush

@section('content')
    <div
        class="article-progress bg-brand-500 fixed top-0 left-0 z-[60] h-0.5 w-full"
        data-article-progress
        aria-hidden="true"
    ></div>

    <article class="article-page" data-article>
        <header class="mx-auto max-w-6xl px-4 pt-12 pb-8 sm:px-6 sm:pt-16 sm:pb-10 lg:px-8 lg:pt-20">
            @if ($post->category)
                <a
                    href="{{ route('blog.category', $post->category) }}"
                    class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase hover:underline"
                >{{ $post->category->name }}</a>
            @endif

            <h1 class="mt-4 max-w-[20ch] text-4xl font-semibold tracking-tight text-pretty text-gray-950 sm:text-5xl lg:text-6xl dark:text-white">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-6 max-w-[68ch] text-lg text-pretty text-gray-600 sm:text-xl dark:text-gray-300">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div class="mt-7 flex flex-wrap items-center gap-x-3 gap-y-2 text-base text-gray-600 sm:text-sm dark:text-gray-400">
                <a
                    href="{{ route('about') }}"
                    class="hover:text-brand-600 dark:hover:text-brand-300 font-medium text-gray-900 dark:text-gray-200"
                >{{ $post->author->name ?? 'Jeffrey Davidson' }}</a>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F d, Y') }}</time>
                <span aria-hidden="true">·</span>
                <span>{{ \App\Presenters\PostPresenter::from($post)->readingTime() }} min read</span>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-post-artwork
                :post="$post"
                sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
                :priority="true"
                class="dark:bg-brand-900 aspect-[3/2] rounded-xl bg-gray-100 outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10"
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
                <details
                    class="dark:bg-brand-900/60 mb-10 rounded-xl bg-gray-50 p-4 outline-1 -outline-offset-1 outline-black/5 lg:hidden dark:outline-white/10"
                    data-article-toc
                    hidden
                >
                    <summary class="cursor-pointer text-base font-semibold text-gray-900 dark:text-white">
                        On this page
                    </summary>
                    <nav class="mt-3" aria-label="Article navigation" data-article-toc-list></nav>
                </details>

                <x-prose class="article-prose prose-a:text-brand-600 dark:prose-a:text-brand-300 prose-code:text-brand-300 max-w-[70ch]">
                    {!!
                        Str::markdown(
                            $post->content,
                            [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false,
                                'heading_permalink' => ['insert' => 'none', 'apply_id_to_heading' => true, 'id_prefix' => ''],
                            ],
                            [new League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension],
                        )
                    !!}
                </x-prose>

                @if ($post->tags->count())
                    <div class="mt-12 border-t border-gray-200 pt-7 dark:border-gray-800">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <x-tag-pill :tag="$tag" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-12 grid gap-5 border-t border-gray-200 pt-8 sm:grid-cols-[4rem_minmax(0,1fr)] sm:items-start dark:border-gray-800">
                    <img
                        src="{{ Vite::asset('resources/images/avatar-320.webp') }}"
                        alt="Jeffrey Davidson"
                        width="64"
                        height="64"
                        loading="lazy"
                        decoding="async"
                        class="size-16 rounded-xl object-cover"
                    />
                    <div>
                        <p class="font-semibold text-gray-950 dark:text-white">Written by Jeffrey Davidson</p>
                        <p class="mt-2 text-base text-pretty text-gray-600 dark:text-gray-400">
                            Laravel developer and software architect sharing practical lessons from building and
                            maintaining real applications.
                        </p>
                        <a
                            href="{{ route('about') }}"
                            class="text-brand-600 dark:text-brand-300 mt-3 inline-flex text-sm font-semibold hover:underline"
                        >More about Jeffrey</a>
                    </div>
                </div>
            </div>
        </div>

        @if ($relatedPosts->count())
            <section
                class="dark:border-brand-800/50 border-t border-gray-200 bg-gray-50 py-14 sm:py-20 dark:bg-[#0b1016]"
                aria-labelledby="related-posts-heading"
            >
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <h2
                        id="related-posts-heading"
                        class="text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl dark:text-white"
                    >
                        Continue reading
                    </h2>
                    <div class="mt-8 grid gap-6 md:grid-cols-2">
                        @foreach ($relatedPosts as $related)
                            <article class="group dark:bg-brand-900/60 overflow-hidden rounded-xl bg-white outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10">
                                <a
                                    href="{{ route('blog.show', $related) }}"
                                    class="focus-visible:outline-brand-500 block focus-visible:outline-2 focus-visible:outline-offset-4"
                                >
                                    <x-post-artwork
                                        :post="$related"
                                        sizes="(min-width: 768px) 560px, calc(100vw - 2rem)"
                                        class="dark:bg-brand-900 aspect-[3/2] bg-gray-100"
                                    />
                                    <div class="p-6">
                                        @if ($related->category)
                                            <span class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase">{{ $related->category->name }}</span>
                                        @endif
                                        <h3 class="group-hover:text-brand-600 dark:group-hover:text-brand-300 mt-2 text-xl font-semibold tracking-tight text-gray-950 transition-colors dark:text-white">
                                            {{ $related->title }}
                                        </h3>
                                        <p class="mt-3 line-clamp-2 text-base text-pretty text-gray-600 dark:text-gray-400">
                                            {{ $related->excerpt }}
                                        </p>
                                        <div class="mt-5 text-sm text-gray-500">
                                            {{ \App\Presenters\PostPresenter::from($related)->readingTime() }} min read
                                        </div>
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
