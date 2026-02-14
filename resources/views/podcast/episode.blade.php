@extends('layouts.app')

@section('title', $episode->title . ' — ' . $podcast->name)

@section('content')
<style>
    .noise-overlay {
        position: relative;
    }
    .noise-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.04;
        pointer-events: none;
        z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat;
        background-size: 256px 256px;
    }
</style>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('podcast.index') }}" class="hover:text-gray-300 transition-colors">Podcasts</a>
            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('podcast.show', $podcast) }}" class="hover:text-gray-300 transition-colors">{{ $podcast->name }}</a>
            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-400 font-mono">{{ $episode->episode_code }}</span>
        </nav>

        {{-- Header --}}
        <header class="mb-10">
            <div class="flex items-center gap-3 mb-4">
                <span class="font-mono text-sm font-semibold px-2.5 py-1 rounded-lg" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">{{ $episode->episode_code }}</span>
                <span class="text-sm text-gray-500">{{ $episode->published_at->format('F d, Y') }}</span>
                @if($episode->formatted_duration)
                <span class="text-sm text-gray-500">· {{ $episode->formatted_duration }}</span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold mb-4 leading-tight">{{ $episode->title }}</h1>
            @if($episode->description)
            <p class="text-lg text-gray-400 leading-relaxed">{{ $episode->description }}</p>
            @endif
        </header>

        {{-- Audio Player --}}
        @if($episode->audio_url)
        <div class="noise-overlay mb-10 p-5 rounded-xl border border-[#1e2a3a] bg-[#0D1117]" style="box-shadow: 0 0 30px {{ $podcast->color }}08;">
            <p class="text-sm font-semibold text-gray-300 mb-3">Listen to this episode</p>
            <audio controls preload="none" class="w-full">
                <source src="{{ $episode->audio_url }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
        @endif

        {{-- Listen/Watch Links --}}
        @if($episode->spotify_url || $episode->apple_podcasts_url || $episode->youtube_url)
        <div class="flex flex-wrap gap-3 mb-10">
            @if($episode->spotify_url)
            <a href="{{ $episode->spotify_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-lg hover:bg-[#1DB954]/20 transition-colors border border-[#1DB954]/20">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                Spotify
            </a>
            @endif
            @if($episode->apple_podcasts_url)
            <a href="{{ $episode->apple_podcasts_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-lg hover:bg-[#D56DFB]/20 transition-colors border border-[#D56DFB]/20">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z"/></svg>
                Apple Podcasts
            </a>
            @endif
            @if($episode->youtube_url)
            <a href="{{ $episode->youtube_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg hover:bg-red-500/20 transition-colors border border-red-500/20">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                Watch on YouTube
            </a>
            @endif
        </div>
        @endif

        {{-- Guest Info --}}
        @if($episode->guest_name)
        <div class="mb-10 p-5 rounded-xl border border-[#1e2a3a] bg-[#0D1117]">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Guest</h3>
            <p class="text-lg font-semibold text-white">{{ $episode->guest_name }}</p>
            @if($episode->guest_title)
            <p class="text-gray-400 text-sm mt-1">{{ $episode->guest_title }}</p>
            @endif
            @if($episode->guest_url)
            <a href="{{ $episode->guest_url }}" target="_blank" class="inline-flex items-center gap-1.5 mt-2 text-sm hover:underline" style="color: {{ $podcast->color }};">
                {{ $episode->guest_url }}
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            @endif
        </div>
        @endif

        {{-- Show Notes --}}
        @if($episode->show_notes)
        <div class="prose prose-invert prose-lg max-w-none mb-12
            prose-headings:text-gray-100 prose-headings:font-extrabold
            prose-a:no-underline hover:prose-a:underline
            prose-code:font-mono prose-pre:bg-[#0D1117] prose-pre:border prose-pre:border-[#1e2a3a]"
            style="--tw-prose-links: {{ $podcast->color }}; --tw-prose-code: #E47A9D;">
            <h2>Show Notes</h2>
            {!! $episode->show_notes !!}
        </div>
        @endif

        {{-- Tags --}}
        @if($episode->tags->count())
        <div class="flex flex-wrap gap-2 mb-12">
            @foreach($episode->tags as $tag)
            <span class="px-3 py-1 text-xs rounded-full border border-[#1e2a3a] text-gray-400">{{ $tag->name }}</span>
            @endforeach
        </div>
        @endif

        {{-- Prev / Next --}}
        <nav class="flex items-center justify-between pt-8 border-t border-[#1e2a3a]">
            @if($prevEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $prevEpisode]) }}" class="group flex items-center gap-3 text-sm max-w-[45%]">
                <svg class="w-5 h-5 text-gray-600 group-hover:-translate-x-1 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <div class="min-w-0">
                    <span class="text-xs text-gray-500">Previous</span>
                    <p class="font-medium group-hover:opacity-80 transition-opacity truncate">{{ Str::limit($prevEpisode->title, 40) }}</p>
                </div>
            </a>
            @else
            <div></div>
            @endif

            @if($nextEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $nextEpisode]) }}" class="group flex items-center gap-3 text-sm text-right max-w-[45%]">
                <div class="min-w-0">
                    <span class="text-xs text-gray-500">Next</span>
                    <p class="font-medium group-hover:opacity-80 transition-opacity truncate">{{ Str::limit($nextEpisode->title, 40) }}</p>
                </div>
                <svg class="w-5 h-5 text-gray-600 group-hover:translate-x-1 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <div></div>
            @endif
        </nav>
    </div>
@endsection
