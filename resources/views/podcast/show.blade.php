@extends('layouts.app')

@push('head')
    @vite('resources/css/pages/podcast-entry.css')
@endpush

@section('content')
    <div class="podcast-detail" style="--podcast-color: {{ $podcast->display_color }};">
        {{-- ===== PODCAST HERO ===== --}}
        <section class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016]">
            <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
                {{-- Breadcrumb --}}
                <a
                    href="{{ route('podcast.index') }}"
                    class="relative z-10 mb-8 inline-flex items-center gap-1.5 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Podcast
                </a>

                <div class="relative z-10 flex flex-col items-center gap-10 md:flex-row">
                    {{-- Artwork --}}
                    <div class="relative flex-shrink-0">
                        @if ($podcast->cover_image_url)
                            <x-podcast-cover
                                :podcast="$podcast"
                                sizes="224px"
                                width="224"
                                height="224"
                                priority
                                class="relative h-48 w-48 rounded-2xl object-cover shadow-2xl ring-1 ring-white/10 md:h-56 md:w-56"
                            />
                        @else
                            <div class="relative flex h-48 w-48 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 shadow-sm md:h-56 md:w-56 dark:border-[#1e2a3a] dark:bg-[#111820]">
                                <svg class="podcast-accent-text h-20 w-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" /></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 text-center md:text-left">
                        {{-- Badge --}}
                        <div class="mb-4 flex items-center justify-center gap-3 md:justify-start">
                            @if ($episodes->count())
                                <span class="podcast-badge inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                    {{ $episodes->total() }} {{ Str::plural('Episode', $episodes->total()) }}
                                </span>
                            @else
                                <span class="podcast-badge inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase">
                                    <span class="podcast-live-dot h-1.5 w-1.5 rounded-full"></span>
                                    Coming Soon
                                </span>
                            @endif

                            {{-- Mini equalizer --}}
                            <div class="flex h-4 items-end gap-[2px]">
                                <span class="eq-bar podcast-accent-bg h-full w-[3px] rounded-full [--dur:0.7s]"></span>
                                <span class="eq-bar podcast-accent-bg h-full w-[3px] rounded-full [--dur:0.5s]"></span>
                                <span class="eq-bar podcast-accent-bg h-full w-[3px] rounded-full [--dur:0.8s]"></span>
                                <span class="eq-bar podcast-accent-bg h-full w-[3px] rounded-full [--dur:0.6s]"></span>
                            </div>
                        </div>

                        <h1 class="mb-4 text-4xl leading-tight font-extrabold text-gray-900 md:text-5xl dark:text-white">
                            {{ $podcast->name }}
                        </h1>
                        <p class="mb-8 max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
                            {{ $podcast->description }}
                        </p>

                        {{-- Subscribe buttons --}}
                        <div class="flex flex-wrap justify-center gap-3 md:justify-start">
                            @if ($podcast->spotify_url)
                                <a
                                    href="{{ $podcast->spotify_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="subscribe-btn inline-flex items-center gap-2 rounded-xl border border-[#1DB954]/20 bg-[#1DB954]/10 px-5 py-2.5 text-sm font-medium text-[#1DB954] transition-colors hover:bg-[#1DB954]/20"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" /></svg>
                                    Spotify
                                </a>
                            @endif
                            @if ($podcast->apple_url)
                                <a
                                    href="{{ $podcast->apple_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="subscribe-btn inline-flex items-center gap-2 rounded-xl border border-[#D56DFB]/20 bg-[#D56DFB]/10 px-5 py-2.5 text-sm font-medium text-[#D56DFB] transition-colors hover:bg-[#D56DFB]/20"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z" /></svg>
                                    Apple Podcasts
                                </a>
                            @endif
                            @if ($podcast->youtube_url)
                                <a
                                    href="{{ $podcast->youtube_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="subscribe-btn inline-flex items-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 px-5 py-2.5 text-sm font-medium text-red-400 transition-colors hover:bg-red-500/20"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
                                    YouTube
                                </a>
                            @endif
                            @if ($podcast->rss_url)
                                <a
                                    href="{{ $podcast->rss_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="subscribe-btn inline-flex items-center gap-2 rounded-xl border border-orange-500/20 bg-orange-500/10 px-5 py-2.5 text-sm font-medium text-orange-400 transition-colors hover:bg-orange-500/20"
                                >
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 010 4.36 2.18 2.18 0 010-4.36M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z" /></svg>
                                    RSS
                                </a>
                            @endif
                            @unless ($podcast->spotify_url || $podcast->apple_url || $podcast->youtube_url || $podcast->rss_url)
                                <span class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 px-5 py-2.5 text-sm text-gray-500 dark:border-[#1e2a3a]">
                                    <span class="podcast-live-dot h-1.5 w-1.5 rounded-full"></span>
                                    Subscribe links coming soon
                                </span>
                            @endunless
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== ABOUT ===== --}}
        @if ($podcast->long_description)
            <section class="border-b border-gray-200 dark:border-[#1e2a3a]">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <h2 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                            About the Show
                        </h2>
                        <div class="space-y-4 text-base leading-relaxed text-gray-600 dark:text-gray-400">
                            @foreach (explode("\n\n", $podcast->long_description) as $paragraph)
                                @if (trim($paragraph))
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ===== LATEST EPISODE FEATURE ===== --}}
        @if ($latestEpisode)
            <section class="border-b border-gray-200 dark:border-[#1e2a3a]">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <a
                        href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}"
                        class="group hover:border-brand-600/50 block overflow-hidden rounded-2xl border border-gray-200 bg-white transition-colors dark:border-[#1e2a3a] dark:bg-[#0D1117]"
                    >
                        {{-- Top accent --}}
                        <div class="podcast-accent-bg h-[2px]"></div>

                        <div class="p-8 md:p-10">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="podcast-badge rounded-full px-3 py-1 text-xs font-semibold tracking-wide uppercase">Latest Episode</span>
                                <span class="font-mono text-sm text-gray-500">{{ \App\Presenters\EpisodePresenter::from($latestEpisode)->code() }}</span>
                                <span class="text-sm text-gray-600">·</span>
                                <time
                                    datetime="{{ $latestEpisode->published_at->toDateString() }}"
                                    class="text-sm text-gray-500"
                                >{{ $latestEpisode->published_at->format('M d, Y') }}</time>
                                @if (\App\Presenters\EpisodePresenter::from($latestEpisode)->duration())
                                    <span class="text-sm text-gray-600">·</span>
                                    <span class="text-sm text-gray-500">{{ \App\Presenters\EpisodePresenter::from($latestEpisode)->duration() }}</span>
                                @endif
                            </div>

                            <h2 class="mb-3 text-2xl font-extrabold text-gray-900 transition-opacity group-hover:opacity-80 md:text-3xl dark:text-white">
                                {{ $latestEpisode->title }}
                            </h2>

                            @if ($latestEpisode->description)
                                <p class="mb-6 max-w-3xl leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ Str::limit($latestEpisode->description, 300) }}
                                </p>
                            @endif

                            @if ($latestEpisode->guest_name)
                                <div class="mb-6 flex items-center gap-3">
                                    <div class="podcast-badge flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold">
                                        {{ substr($latestEpisode->guest_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $latestEpisode->guest_name }}</span>
                                        @if ($latestEpisode->guest_title)
                                            <span class="text-sm text-gray-600">
                                                · {{ $latestEpisode->guest_title }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="podcast-accent-text inline-flex items-center gap-2 text-sm font-semibold">
                                <div class="podcast-accent-bg flex h-10 w-10 items-center justify-center rounded-full transition-transform group-hover:scale-110">
                                    <svg class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                </div>
                                <span>Listen Now</span>
                            </div>
                        </div>
                    </a>
                </div>
            </section>
        @endif

        {{-- ===== ALL EPISODES ===== --}}
        <section class="bg-gray-50 dark:bg-[#0b1016]">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        @if ($episodes->count()) All Episodes @else Episodes @endif
                    </h2>
                    @if ($episodes->total() > 0)
                        <span class="font-mono text-sm text-gray-500">{{ $episodes->total() }} total</span>
                    @endif
                </div>

                @if ($episodes->count())
                    <div class="space-y-2">
                        @foreach ($episodes as $episode)
                            <a
                                href="{{ route('podcast.episode', [$podcast, $episode]) }}"
                                class="episode-card group flex items-center gap-5 rounded-xl border border-gray-200 bg-white p-4 md:p-5 dark:border-[#1e2a3a] dark:bg-[#0D1117]/50"
                            >
                                {{-- Episode number / play icon --}}
                                <div class="podcast-accent-bg-faint relative flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl">
                                    <span class="ep-number podcast-accent-text font-mono text-xs font-bold">{{ \App\Presenters\EpisodePresenter::from($episode)->code() }}</span>
                                    <div class="ep-play absolute inset-0 flex items-center justify-center">
                                        <div class="podcast-accent-bg flex h-10 w-10 items-center justify-center rounded-full">
                                            <svg class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-gray-900 transition-opacity group-hover:opacity-80 dark:text-white">
                                        {{ $episode->title }}
                                    </h3>
                                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                        <time datetime="{{ $episode->published_at->toDateString() }}">{{ $episode->published_at->format('M d, Y') }}</time>
                                        @if (\App\Presenters\EpisodePresenter::from($episode)->duration())
                                            <span class="text-gray-300 dark:text-gray-700">·</span>
                                            <span>{{ \App\Presenters\EpisodePresenter::from($episode)->duration() }}</span>
                                        @endif
                                        @if ($episode->guest_name)
                                            <span class="hidden text-gray-300 sm:inline dark:text-gray-700">·</span>
                                            <span class="hidden sm:inline"
                                                >with
                                                <span
                                                    class="podcast-accent-text"
                                                    >{{ $episode->guest_name }}</span
                                                ></span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Mini equalizer on hover --}}
                                <div class="hidden h-5 items-end gap-[2px] opacity-0 transition-opacity group-hover:opacity-60 md:flex">
                                    <span class="eq-bar podcast-accent-bg h-full w-[2px] rounded-full [--dur:0.6s]"></span>
                                    <span class="eq-bar podcast-accent-bg h-full w-[2px] rounded-full [--dur:0.8s]"></span>
                                    <span class="eq-bar podcast-accent-bg h-full w-[2px] rounded-full [--dur:0.5s]"></span>
                                    <span class="eq-bar podcast-accent-bg h-full w-[2px] rounded-full [--dur:0.7s]"></span>
                                </div>

                                <svg class="h-5 w-5 flex-shrink-0 text-gray-600 transition-transform group-hover:translate-x-1 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $episodes->links() }}</div>
                @else
                    <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center dark:border-[#1e2a3a]">
                        <div class="podcast-accent-bg-soft mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl">
                            <svg class="podcast-accent-text h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" /></svg>
                        </div>
                        <p class="mb-2 text-lg font-medium text-gray-600 dark:text-gray-400">No episodes yet</p>
                        <p class="text-sm text-gray-500">First episodes are in the works. Check back soon!</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
