@extends('layouts.app')

@push('head')
    @vite('resources/css/pages/podcast-entry.css')
@endpush

@section('content')
<div class="podcast-detail" style="--podcast-color: {{ $podcast->display_color }};">
{{-- ===== EPISODE HERO ===== --}}
<section class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016]">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-16">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8 relative z-10">
            <a href="{{ route('podcast.index') }}" class="hover:text-gray-300 dark:text-gray-700 dark:hover:text-gray-300 transition-colors">Podcast</a>
            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('podcast.show', $podcast) }}" class="hover:text-gray-300 dark:text-gray-700 dark:hover:text-gray-300 transition-colors">{{ $podcast->name }}</a>
            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $episode->episode_code }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-center relative z-10">
            {{-- Podcast artwork --}}
            <div class="flex-shrink-0 relative">
                @if($podcast->cover_image_url)
                <x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224" priority class="relative h-48 w-48 rounded-2xl object-cover shadow-2xl ring-1 ring-white/10 lg:h-56 lg:w-56" />
                @else
                <div class="relative flex h-48 w-48 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 shadow-sm dark:border-[#1e2a3a] dark:bg-[#111820] lg:h-56 lg:w-56">
                    <svg class="podcast-accent-text w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                </div>
                @endif
            </div>

            {{-- Episode info --}}
            <div class="flex-1 min-w-0 text-center lg:text-left">
                {{-- Meta badges --}}
                <div class="flex flex-wrap items-center gap-3 mb-4 justify-center lg:justify-start">
                    <span class="podcast-badge font-mono text-sm font-bold px-3 py-1.5 rounded-lg">{{ $episode->episode_code }}</span>
                    <span class="text-sm text-gray-500">{{ $episode->published_at->format('F d, Y') }}</span>
                    @if($episode->formatted_duration)
                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $episode->formatted_duration }}
                    </span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 leading-tight">{{ $episode->title }}</h1>

                @if($episode->description)
                <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed mb-6 max-w-3xl">{{ $episode->description }}</p>
                @endif

                {{-- Podcast name link --}}
                <a href="{{ route('podcast.show', $podcast) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 dark:text-gray-700 dark:hover:text-gray-300 transition-colors">
                    @if($podcast->cover_image_url)
                    <x-podcast-cover :podcast="$podcast" alt="" sizes="20px" width="20" height="20" class="h-5 w-5 rounded object-cover" />
                    @else
                    <svg class="podcast-accent-text w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                    @endif
                    {{ $podcast->name }}
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ===== MAIN CONTENT ===== --}}
<div class="bg-gray-50 dark:bg-[#0b1016]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="flex flex-col lg:flex-row gap-12">

            {{-- Left Column --}}
            <div class="flex-1 min-w-0">

                {{-- Custom Audio Player --}}
                @if($episode->audio_url)
                <div class="mb-10 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0D1117]" data-audio-player data-playing="false">
                    <audio controls data-audio preload="metadata">
                        <source src="{{ $episode->audio_url }}" type="audio/mpeg">
                    </audio>

                    {{-- Waveform visualization --}}
                    <div class="px-6 pt-6 pb-2">
                        <div class="flex h-16 items-end justify-center gap-[2px] opacity-40" aria-hidden="true">
                            @for($i = 0; $i < 80; $i++)
                            <div class="waveform-bar podcast-accent-bg h-full w-[3px] rounded-full" style="--from: {{ (10 + (($i * 7) % 21)) / 100 }}; --to: {{ (40 + (($i * 13) % 61)) / 100 }}; --dur: {{ (4 + (($i * 5) % 9)) / 10 }}s; animation-delay: {{ $i * 0.04 }}s;"></div>
                            @endfor
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="px-6 py-2">
                        <div class="group relative h-5">
                            <div class="pointer-events-none absolute inset-x-0 top-1/2 h-1.5 -translate-y-1/2 rounded-full bg-[#1e2a3a]">
                                <div class="podcast-accent-bg absolute inset-y-0 left-0 w-0 rounded-full" data-audio-progress></div>
                            </div>
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="0.1"
                                value="0"
                                data-audio-seek
                                aria-label="Seek episode"
                                aria-valuetext="0:00 of 0:00"
                                class="absolute inset-0 h-5 w-full cursor-pointer opacity-0"
                            >
                        </div>
                        <div class="mt-2 flex justify-between font-mono text-xs text-gray-600">
                            <span data-audio-current-time>0:00</span>
                            <span data-audio-duration>0:00</span>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div class="px-6 pb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            {{-- Podcast mini artwork --}}
                            @if($podcast->cover_image_url)
                            <x-podcast-cover :podcast="$podcast" alt="" sizes="40px" width="40" height="40" class="h-10 w-10 rounded-lg object-cover" />
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $episode->title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $podcast->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            {{-- Skip back 15s --}}
                            <button type="button" data-audio-skip-back aria-label="Skip back 15 seconds" class="relative text-gray-500 transition-colors hover:text-gray-900 dark:hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/></svg>
                                <span class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 font-mono text-[10px] text-gray-600">15</span>
                            </button>

                            {{-- Play/Pause --}}
                            <button type="button" data-audio-play aria-label="Play episode" aria-pressed="false" class="podcast-accent-bg flex h-12 w-12 items-center justify-center rounded-full text-white transition-transform hover:scale-105">
                                <svg data-audio-play-icon aria-hidden="true" class="ml-0.5 h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <svg data-audio-pause-icon hidden aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
                            </button>

                            {{-- Skip forward 30s --}}
                            <button type="button" data-audio-skip-forward aria-label="Skip forward 30 seconds" class="relative text-gray-500 transition-colors hover:text-gray-900 dark:hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z"/></svg>
                                <span class="absolute -bottom-3.5 left-1/2 -translate-x-1/2 font-mono text-[10px] text-gray-600">30</span>
                            </button>
                        </div>

                        {{-- Speed control --}}
                        <div>
                            <button type="button" data-audio-speed aria-label="Playback speed 1 times. Activate to change." class="rounded-lg border border-gray-200 px-2.5 py-1 font-mono text-xs text-gray-600 transition-colors hover:border-gray-600 hover:text-gray-900 dark:border-[#1e2a3a] dark:text-gray-400 dark:hover:text-white">
                                <span data-audio-speed-label>1x</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Description Fallback (no audio, no show_notes, no youtube) --}}
                @if(!$episode->audio_url && !$episode->show_notes && !($episode->youtube_url && str_contains($episode->youtube_url, 'youtu')))
                <div class="relative mb-10 rounded-2xl border border-gray-200 bg-white p-8 dark:border-[#1e2a3a] dark:bg-[#0D1117]">
                    <div class="podcast-accent-text absolute top-6 left-6 text-6xl leading-none opacity-15">"</div>
                    <div class="pl-8 pt-4">
                        <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300 leading-relaxed italic">{{ $episode->description }}</p>
                    </div>
                </div>
                @endif

                {{-- YouTube Embed --}}
                @if($episode->youtube_url && str_contains($episode->youtube_url, 'youtu'))
                <div class="mb-10 rounded-2xl overflow-hidden border border-gray-200 dark:border-[#1e2a3a]">
                    @php
                        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $episode->youtube_url, $matches);
                        $videoId = $matches[1] ?? null;
                    @endphp
                    @if($videoId)
                    <div class="relative aspect-video w-full bg-[#0b1016]" data-youtube-facade>
                        <template data-youtube-player>
                            <iframe
                                src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1"
                                title="{{ $episode->title }} on YouTube"
                                class="absolute inset-0 h-full w-full border-0"
                                allow="autoplay; encrypted-media; picture-in-picture"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        </template>

                        <button
                            type="button"
                            data-youtube-play
                            aria-label="Play {{ $episode->title }} on YouTube"
                            class="group absolute inset-0 flex w-full flex-col items-center justify-center gap-4 px-6 text-center text-white transition-colors hover:bg-white/[0.03] focus-visible:outline-2 focus-visible:outline-offset-[-4px] focus-visible:outline-brand-400"
                        >
                            <span class="flex h-16 w-16 items-center justify-center rounded-full border border-white/20 bg-white/10 transition-transform group-hover:scale-105" aria-hidden="true">
                                <svg class="ml-1 h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                            <span>
                                <span class="block font-mono text-xs uppercase tracking-[0.18em] text-brand-300">Video episode</span>
                                <span class="mt-2 block text-lg font-semibold">Watch on YouTube</span>
                            </span>
                        </button>

                        <noscript>
                            <a href="{{ $episode->youtube_url }}" target="_blank" rel="noopener noreferrer" class="absolute inset-0 flex items-center justify-center text-base font-semibold text-white underline decoration-brand-400 underline-offset-4">Watch on YouTube</a>
                        </noscript>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Listen/Watch Platform Links --}}
                @if($episode->spotify_url || $episode->apple_podcasts_url || $episode->youtube_url)
                <div class="flex flex-wrap gap-3 mb-10">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide self-center mr-2">Listen on</span>
                    @if($episode->spotify_url)
                    <a href="{{ $episode->spotify_url }}" target="_blank" rel="noopener noreferrer" class="share-btn inline-flex items-center gap-2 px-4 py-2.5 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-lg border border-[#1DB954]/20 hover:bg-[#1DB954]/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        Spotify
                    </a>
                    @endif
                    @if($episode->apple_podcasts_url)
                    <a href="{{ $episode->apple_podcasts_url }}" target="_blank" rel="noopener noreferrer" class="share-btn inline-flex items-center gap-2 px-4 py-2.5 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-lg border border-[#D56DFB]/20 hover:bg-[#D56DFB]/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z"/></svg>
                        Apple Podcasts
                    </a>
                    @endif
                    @if($episode->youtube_url)
                    <a href="{{ $episode->youtube_url }}" target="_blank" rel="noopener noreferrer" class="share-btn inline-flex items-center gap-2 px-4 py-2.5 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg border border-red-500/20 hover:bg-red-500/20 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        YouTube
                    </a>
                    @endif
                </div>
                @endif

                {{-- Guest Feature Card --}}
                @if($episode->guest_name)
                <div class="mb-10 p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Featured Guest</h3>
                    <div class="flex items-start gap-4">
                        <div class="podcast-badge w-14 h-14 rounded-full flex-shrink-0 flex items-center justify-center text-xl font-bold">
                            {{ substr($episode->guest_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $episode->guest_name }}</p>
                            @if($episode->guest_title)
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-0.5">{{ $episode->guest_title }}</p>
                            @endif
                            @if($episode->guest_url)
                            <a href="{{ $episode->guest_url }}" target="_blank" rel="noopener noreferrer" class="podcast-accent-text inline-flex items-center gap-1.5 mt-2 text-sm hover:underline">
                                {{ parse_url($episode->guest_url, PHP_URL_HOST) }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Show Notes --}}
                @if($episode->show_notes)
                <div class="mb-12">
                    <h2 class="text-2xl font-extrabold mb-6 flex items-center gap-3">
                        <span class="podcast-accent-bg-soft w-8 h-8 rounded-lg flex items-center justify-center">
                            <svg class="podcast-accent-text w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        Show Notes
                    </h2>
                    <div class="podcast-prose prose prose-invert prose-lg max-w-none
                        prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-headings:font-extrabold
                        prose-a:no-underline hover:prose-a:underline
                        prose-code:font-mono prose-pre:bg-gray-50 dark:prose-pre:bg-[#0D1117] prose-pre:border prose-pre:border-gray-200 dark:prose-pre:border-[#1e2a3a]
                        prose-li:text-gray-600 dark:prose-li:text-gray-400 prose-p:text-gray-600 dark:prose-p:text-gray-400">
                        {!! $episode->show_notes !!}
                    </div>
                </div>
                @endif

                {{-- Tags --}}
                @if($episode->tags->count())
                <div class="mb-12">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Topics</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($episode->tags as $tag)
                        <span class="px-3 py-1.5 text-xs font-medium rounded-full border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:border-gray-600 transition-colors">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Empty State Fallback --}}
                @if(!$episode->audio_url && !$episode->show_notes && !$episode->guest_name && !$episode->tags->count() && !($episode->youtube_url && str_contains($episode->youtube_url, 'youtu')) && !$episode->spotify_url && !$episode->apple_podcasts_url)
                <div class="mb-12 p-8 rounded-2xl border border-dashed border-gray-200 dark:border-[#1e2a3a] text-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">Full episode details coming soon</p>
                    <p class="text-sm text-gray-600 mt-2">Show notes, links, and more will be added shortly.</p>
                </div>
                @endif
            </div>

            {{-- Right Sidebar --}}
            <div class="lg:w-80 flex-shrink-0">
                <div class="lg:sticky lg:top-8 space-y-6">

                    {{-- About This Podcast --}}
                    <div class="p-5 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">About This Podcast</h3>
                        <a href="{{ route('podcast.show', $podcast) }}" class="group block">
                            <div class="flex items-center gap-3 mb-3">
                                @if($podcast->cover_image_url)
                                <x-podcast-cover :podcast="$podcast" sizes="48px" width="48" height="48" class="h-12 w-12 rounded-lg object-cover" />
                                @else
                                <div class="podcast-accent-bg-soft w-12 h-12 rounded-lg flex items-center justify-center">
                                    <svg class="podcast-accent-text w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm group-hover:opacity-80 transition-opacity truncate">{{ $podcast->name }}</p>
                                    <p class="text-xs text-gray-500">View all episodes →</p>
                                </div>
                            </div>
                        </a>
                        <p class="text-gray-500 text-xs leading-relaxed">{{ Str::limit($podcast->description, 150) }}</p>
                    </div>

                    {{-- Episode Details --}}
                    <div class="p-5 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Episode Details</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Episode</dt>
                                <dd class="podcast-accent-text font-mono font-semibold">{{ $episode->episode_code }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Published</dt>
                                <dd class="text-gray-300">{{ $episode->published_at->format('M d, Y') }}</dd>
                            </div>
                            @if($episode->formatted_duration)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Duration</dt>
                                <dd class="text-gray-300">{{ $episode->formatted_duration }}</dd>
                            </div>
                            @endif
                            @if($episode->season_number)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Season</dt>
                                <dd class="text-gray-300">{{ $episode->season_number }}</dd>
                            </div>
                            @endif
                            @if($episode->guest_name)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Guest</dt>
                                <dd class="text-gray-300">{{ $episode->guest_name }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Share Episode --}}
                    <div class="p-5 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Share Episode</h3>
                        <div class="flex gap-2">
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — ' . $podcast->name) }}&url={{ urlencode(route('podcast.episode', [$podcast, $episode])) }}" target="_blank" rel="noopener noreferrer" class="share-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-600 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('podcast.episode', [$podcast, $episode])) }}&title={{ urlencode($episode->title) }}" target="_blank" rel="noopener noreferrer" class="share-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-600 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                            <button type="button" data-podcast-copy-url="{{ route('podcast.episode', [$podcast, $episode]) }}" aria-label="Copy episode link" class="share-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-600 transition-colors text-sm cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Prev / Next Navigation --}}
        <div class="mt-16 pt-8 border-t border-gray-200 dark:border-[#1e2a3a]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($prevEpisode)
                <a href="{{ route('podcast.episode', [$podcast, $prevEpisode]) }}" class="ep-nav group p-5 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] transition-all duration-300 hover:bg-white dark:bg-[#0D1117]">
                    <div class="flex items-center gap-3">
                        <svg class="ep-nav-arrow w-5 h-5 text-gray-600 flex-shrink-0 transition-transform [--arrow-dir:-4px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <div class="min-w-0">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Previous Episode</span>
                            <p class="font-semibold group-hover:opacity-80 transition-opacity truncate mt-0.5">{{ $prevEpisode->title }}</p>
                            <span class="text-xs font-mono text-gray-500">{{ $prevEpisode->episode_code }}@if($prevEpisode->formatted_duration) · {{ $prevEpisode->formatted_duration }}@endif</span>
                        </div>
                    </div>
                </a>
                @else
                <div></div>
                @endif

                @if($nextEpisode)
                <a href="{{ route('podcast.episode', [$podcast, $nextEpisode]) }}" class="ep-nav group p-5 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] transition-all duration-300 hover:bg-white dark:bg-[#0D1117] text-right">
                    <div class="flex items-center justify-end gap-3">
                        <div class="min-w-0">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Next Episode</span>
                            <p class="font-semibold group-hover:opacity-80 transition-opacity truncate mt-0.5">{{ $nextEpisode->title }}</p>
                            <span class="text-xs font-mono text-gray-500">{{ $nextEpisode->episode_code }}@if($nextEpisode->formatted_duration) · {{ $nextEpisode->formatted_duration }}@endif</span>
                        </div>
                        <svg class="ep-nav-arrow w-5 h-5 text-gray-600 flex-shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                @else
                <div></div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
