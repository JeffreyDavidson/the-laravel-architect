@extends('layouts.app')

@section('title', 'Search')

@section('content')
    <div class="dark:bg-surface-page min-h-[60vh] bg-gray-50">
        <header class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
                <p class="text-brand-600 tracking-label font-mono text-xs uppercase">Search the archive</p>
                <h1 class="mt-4 text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                    Find something useful.
                </h1>
                <form method="GET" action="{{ route('search') }}" role="search" class="mt-8 max-w-2xl">
                    <div class="relative">
                        <label for="site-search" class="sr-only">Search the site</label>
                        <x-svg-icon
                            name="search"
                            class="text-brand-600 pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2"
                        />
                        <input
                            id="site-search"
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            maxlength="120"
                            autofocus
                            placeholder="Search writing, projects, podcasts, and videos"
                            class="focus:border-brand-600 focus:ring-brand-600 dark:border-brand-700 dark:bg-brand-950 w-full rounded-xl border border-gray-300 bg-white py-3.5 pr-28 pl-12 text-base text-gray-900 placeholder-gray-500 focus:ring-2 focus:outline-none dark:text-gray-100"
                        />
                        <button
                            type="submit"
                            class="bg-brand-600 hover:bg-brand-500 absolute top-1/2 right-2 -translate-y-1/2 rounded-lg px-4 py-2 text-sm font-semibold text-white"
                        >
                            Search
                        </button>
                    </div>
                    <div class="mt-4 max-w-xs">
                        <label
                            for="search-type"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Filter by content type
                        </label>
                        <x-form.select
                            id="search-type"
                            name="type"
                            :options="$typeOptions"
                            placeholder="All content"
                            :value="$selectedType"
                        />
                    </div>
                </form>
            </div>
        </header>

        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            @if ($query === '')
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Search across the public archive to find an article, project, episode, podcast, or video.
                </p>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400" aria-live="polite" role="status">
                    {{ $resultCount }} {{ \Illuminate\Support\Str::plural('result', $resultCount) }} for
                    <span class="font-semibold text-gray-900 dark:text-gray-100">“{{ $query }}”</span>
                </p>

                @if ($resultCount === 0)
                    <div class="dark:border-surface-border mt-8 border-y border-gray-200 py-16 text-center">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            Nothing matched your search.
                        </h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Try a broader term or search for a technology, topic, or guest.
                        </p>
                    </div>
                @else
                    <div class="mt-8 space-y-12">
                        @foreach ($results as $type => $items)
                            @if (count($items))
                                <section aria-labelledby="search-{{ \Illuminate\Support\Str::slug($type) }}">
                                    <div class="dark:border-surface-border mb-4 flex items-baseline justify-between border-b border-gray-200 pb-3">
                                        <h2
                                            id="search-{{ \Illuminate\Support\Str::slug($type) }}"
                                            class="text-xl font-semibold text-gray-900 dark:text-white"
                                        >
                                            {{ $type }}
                                        </h2>
                                        <span class="font-mono text-xs text-gray-500">{{ count($items) }}</span>
                                    </div>
                                    <div class="dark:divide-brand-800 divide-y divide-gray-200">
                                        @foreach ($items as $item)
                                            <article class="py-5 first:pt-0 last:pb-0">
                                                <a
                                                    href="{{ $item['url'] }}"
                                                    @if ($item['external']) target="_blank" rel="noreferrer" @endif
                                                    class="group focus-visible:outline-brand-500 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4"
                                                >
                                                    <div class="flex items-center gap-3 font-mono text-xs tracking-wide text-gray-500 uppercase">
                                                        <span>{{ $item['meta'] }}</span>
                                                        @if ($item['external'])
                                                            <span aria-hidden="true">↗</span>
                                                            <span class="sr-only">(opens in a new tab)</span>
                                                        @endif
                                                    </div>
                                                    <h3 class="group-hover:text-brand-600 mt-2 text-xl font-semibold text-gray-900 transition-colors dark:text-white">
                                                        {!! $item['highlightedTitle'] !!}
                                                    </h3>
                                                    @if ($item['highlightedDescription'])
                                                        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                                                            {!! $item['highlightedDescription'] !!}
                                                        </p>
                                                    @endif
                                                </a>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
