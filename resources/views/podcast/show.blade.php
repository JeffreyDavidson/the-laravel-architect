@extends('layouts.app')

@section('title', $podcast->name)

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
    .dot-grid-bg {
        position: relative;
    }
    .dot-grid-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.03;
        pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px;
        z-index: 0;
    }
    .dot-grid-bg > * {
        position: relative;
        z-index: 1;
    }
    .episode-row {
        transition: all 0.2s ease;
    }
    .episode-row:hover {
        background: rgba(255, 255, 255, 0.02);
    }
</style>

    {{-- Podcast Header --}}
    <div class="noise-overlay border-b border-[#1e2a3a]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="flex flex-col md:flex-row items-start gap-8">
                {{-- Artwork --}}
                @if($podcast->cover_image)
                <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-40 h-40 rounded-2xl object-cover flex-shrink-0 shadow-2xl">
                @else
                <div class="w-40 h-40 rounded-2xl flex-shrink-0 shadow-2xl flex items-center justify-center" style="background: linear-gradient(135deg, {{ $podcast->color }}44, {{ $podcast->color }}11);">
                    <svg class="w-16 h-16" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                </div>
                @endif

                <div class="flex-1">
                    <a href="{{ route('podcast.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-300 transition-colors mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        All Podcasts
                    </a>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-3" style="color: {{ $podcast->color }};">{{ $podcast->name }}</h1>
                    <p class="text-gray-400 text-lg mb-6 max-w-2xl leading-relaxed">{{ $podcast->description }}</p>

                    {{-- Subscribe buttons --}}
                    <div class="flex flex-wrap gap-3">
                        @if($podcast->spotify_url)
                        <a href="{{ $podcast->spotify_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-lg hover:bg-[#1DB954]/20 transition-colors border border-[#1DB954]/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                            Spotify
                        </a>
                        @endif
                        @if($podcast->apple_url)
                        <a href="{{ $podcast->apple_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-lg hover:bg-[#D56DFB]/20 transition-colors border border-[#D56DFB]/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34zm6.525 2.568c2.336 0 4.448.902 6.025 2.392a8.07 8.07 0 012.497 5.173 1.053 1.053 0 01-2.095.168 5.994 5.994 0 00-1.855-3.84 5.93 5.93 0 00-4.572-1.78 5.988 5.988 0 00-5.552 4.26 1.053 1.053 0 01-2.03-.558 8.088 8.088 0 017.582-5.815zm.098 3.108a4.7 4.7 0 013.695 1.705 4.67 4.67 0 011.17 3.392 1.053 1.053 0 01-2.1-.09 2.593 2.593 0 00-.65-1.883 2.607 2.607 0 00-2.048-.944 2.6 2.6 0 00-2.56 2.163 1.053 1.053 0 01-2.074-.37 4.706 4.706 0 014.567-3.973zm-.228 4.392c.68 0 1.303.27 1.757.713.455.442.72 1.052.72 1.727 0 .457-.136.912-.393 1.378-.264.478-.6.87-.862 1.142l-.105.112-1.182 2.947a1.018 1.018 0 01-1.872 0l-1.182-2.947-.105-.112c-.262-.272-.598-.664-.862-1.142-.257-.466-.393-.921-.393-1.378 0-.675.265-1.285.72-1.727a2.49 2.49 0 011.759-.713z"/></svg>
                            Apple Podcasts
                        </a>
                        @endif
                        @if($podcast->youtube_url)
                        <a href="{{ $podcast->youtube_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg hover:bg-red-500/20 transition-colors border border-red-500/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            YouTube
                        </a>
                        @endif
                        @if($podcast->rss_url)
                        <a href="{{ $podcast->rss_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-orange-500/10 text-orange-400 text-sm font-medium rounded-lg hover:bg-orange-500/20 transition-colors border border-orange-500/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 010 4.36 2.18 2.18 0 010-4.36M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z"/></svg>
                            RSS
                        </a>
                        @endif

                        {{-- No subscribe links yet --}}
                        @unless($podcast->spotify_url || $podcast->apple_url || $podcast->youtube_url || $podcast->rss_url)
                        <span class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm text-gray-500 border border-[#1e2a3a] rounded-lg">
                            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $podcast->color }};"></span>
                            Subscribe links coming soon
                        </span>
                        @endunless
                    </div>
                </div>
            </div>

            {{-- Long description --}}
            @if($podcast->long_description)
            <div class="mt-8 pt-8 border-t border-[#1e2a3a] max-w-3xl">
                <p class="text-gray-400 leading-relaxed whitespace-pre-line">{{ $podcast->long_description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Episodes --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            @if($latestEpisode)
            {{-- Featured Latest Episode --}}
            <div class="mb-12 p-6 md:p-8 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]" style="box-shadow: 0 0 40px {{ $podcast->color }}08;">
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">Latest Episode</span>
                    <span class="font-mono text-sm" style="color: {{ $podcast->color }};">{{ $latestEpisode->episode_code }}</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold mb-2">
                    <a href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}" class="hover:opacity-80 transition-opacity" style="color: {{ $podcast->color }};">{{ $latestEpisode->title }}</a>
                </h2>
                <div class="flex items-center gap-3 text-sm text-gray-500 mb-4">
                    <span>{{ $latestEpisode->published_at->format('M d, Y') }}</span>
                    @if($latestEpisode->formatted_duration)
                    <span>· {{ $latestEpisode->formatted_duration }}</span>
                    @endif
                    @if($latestEpisode->guest_name)
                    <span>· with <span style="color: {{ $podcast->color }};">{{ $latestEpisode->guest_name }}</span></span>
                    @endif
                </div>
                @if($latestEpisode->description)
                <p class="text-gray-400 text-sm leading-relaxed mb-5 max-w-3xl">{{ Str::limit($latestEpisode->description, 300) }}</p>
                @endif
                @if($latestEpisode->audio_url)
                <audio controls preload="none" class="w-full max-w-2xl rounded-lg">
                    <source src="{{ $latestEpisode->audio_url }}" type="audio/mpeg">
                </audio>
                @endif
            </div>
            @endif

            {{-- Episode List --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">
                    @if($episodes->count())
                        All Episodes
                    @else
                        Episodes
                    @endif
                </h2>
            </div>

            @if($episodes->count())
            <div class="space-y-1 rounded-xl overflow-hidden border border-[#1e2a3a]">
                @foreach($episodes as $episode)
                <a href="{{ route('podcast.episode', [$podcast, $episode]) }}" class="episode-row group flex items-center gap-5 p-4 md:p-5 @if(!$loop->last) border-b border-[#1e2a3a] @endif">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" style="background: {{ $podcast->color }}10;">
                        <span class="font-mono text-xs font-semibold" style="color: {{ $podcast->color }};">{{ $episode->episode_code }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold group-hover:opacity-80 transition-opacity truncate">{{ $episode->title }}</h3>
                        <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                            <span>{{ $episode->published_at->format('M d, Y') }}</span>
                            @if($episode->formatted_duration)
                            <span>· {{ $episode->formatted_duration }}</span>
                            @endif
                            @if($episode->guest_name)
                            <span class="hidden sm:inline">· with {{ $episode->guest_name }}</span>
                            @endif
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-600 group-hover:translate-x-1 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $episodes->links() }}
            </div>
            @else
            <div class="text-center py-16 rounded-xl border border-[#1e2a3a]">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: {{ $podcast->color }}10;">
                    <svg class="w-8 h-8" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                </div>
                <p class="text-gray-400 text-lg font-medium mb-2">No episodes yet</p>
                <p class="text-gray-500 text-sm">First episodes are in the works. Check back soon!</p>
            </div>
            @endif
        </div>
    </div>
@endsection
