@extends('layouts.app')

@section('title', 'Podcast')

@push('head')
    @vite('resources/css/pages/podcast-entry.css')
@endpush

@section('content')
<x-hero-section>
    <div class="grid gap-8 lg:grid-cols-[8rem_minmax(0,1fr)] lg:gap-10">
        <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Audio / 03</p>
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">Coffee, code, and the decisions between them.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">Coffee with The Laravel Architect is where Laravel, architecture, and the developer life meet.</p>
            <div class="mt-7 flex gap-6 font-mono text-xs uppercase tracking-wide text-gray-500">
                <span>{{ $podcast ? '1 show' : 'No show yet' }}</span>
                <span>{{ $podcast?->published_episodes_count ?? 0 }} episodes</span>
            </div>
        </div>
    </div>
</x-hero-section>

<div class="bg-gray-50 dark:bg-[#0b1016]">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-20 lg:px-8">
        @if($podcast)
            <a href="{{ route('podcast.show', $podcast) }}" class="group grid gap-10 border-y border-gray-200 py-10 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-400 md:grid-cols-[18rem_minmax(0,1fr)] md:items-center md:py-14 dark:border-[#1e2a3a]">
                @if($podcast->cover_image_url)
                    <x-podcast-cover :podcast="$podcast" sizes="288px" width="288" height="288" priority class="aspect-square w-full max-w-72 object-cover grayscale-[15%]" />
                @else
                    <div class="flex aspect-square w-full max-w-72 items-center justify-center border border-[#1e2a3a] bg-[#101722]">
                        <x-svg-icon name="microphone" class="h-12 w-12 text-brand-600" />
                    </div>
                @endif

                <div>
                    <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Current show</p>
                    <h2 class="mt-4 text-3xl font-semibold text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white md:text-5xl">{{ $podcast->name }}</h2>
                    <p class="mt-5 max-w-2xl text-base leading-relaxed text-gray-600 dark:text-gray-400">{{ $podcast->description }}</p>
                    <ul aria-label="Topics" class="mt-7 flex flex-wrap gap-x-5 gap-y-2 font-mono text-xs uppercase tracking-wide text-gray-500">
                        @foreach(['Laravel', 'Architecture', 'Testing', 'Career', 'Guest interviews'] as $topic)<li>{{ $topic }}</li>@endforeach
                    </ul>
                    <span class="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-brand-600">View show <x-svg-icon name="arrow-long-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                </div>
            </a>

            <section aria-labelledby="podcast-format-heading" class="mt-20">
                <div class="mb-8 grid gap-3 border-b border-gray-200 pb-5 md:grid-cols-[10rem_1fr] dark:border-[#1e2a3a]">
                    <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Format</p>
                    <h2 id="podcast-format-heading" class="text-2xl font-semibold text-gray-900 dark:text-white">What you will hear</h2>
                </div>
                <div class="grid md:grid-cols-3">
                    @foreach([
                        ['Architecture deep dives', 'Real-world Laravel patterns, testing strategies, and the decisions behind production code. No toy examples.'],
                        ['Practical takeaways', 'Clear explanations, tradeoffs, and decisions you can use in real Laravel projects without sitting through filler.'],
                        ['Guest conversations', 'Developers, creators, and thinkers sharing war stories, mistakes, and the lessons they carry forward.'],
                    ] as [$title, $description])
                        <article class="border-b border-gray-200 py-7 md:border-b-0 md:border-r md:px-7 md:first:pl-0 md:last:border-r-0 dark:border-[#1e2a3a]">
                            <p class="font-mono text-xs text-gray-500">0{{ $loop->iteration }}</p>
                            <h3 class="mt-5 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $description }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @else
            <div class="border-y border-gray-200 py-20 text-center dark:border-[#1e2a3a]">
                <p class="font-mono text-xs uppercase tracking-[0.18em] text-gray-500">Recording in progress</p>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Podcast launching soon. Stay tuned.</p>
            </div>
        @endif
    </div>
</div>
@endsection
