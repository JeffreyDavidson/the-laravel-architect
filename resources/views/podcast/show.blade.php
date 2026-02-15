@extends('layouts.app')

@section('title', $podcast->name)

@section('content')
<style>
    .noise-overlay { position: relative; }
    .noise-overlay::after {
        content: ''; position: absolute; inset: 0; opacity: 0.04; pointer-events: none; z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat; background-size: 256px 256px;
    }
    .dot-grid-bg { position: relative; }
    .dot-grid-bg::before {
        content: ''; position: absolute; inset: 0; opacity: 0.03; pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px; z-index: 0;
    }
    .dot-grid-bg > * { position: relative; z-index: 1; }

    @keyframes glowPulse {
        0%, 100% { opacity: 0.25; transform: translate(-50%, -50%) scale(1); }
        50% { opacity: 0.45; transform: translate(-50%, -50%) scale(1.08); }
    }
    .artwork-glow { animation: glowPulse 4s ease-in-out infinite; }

    .eq-bar {
        animation: equalize var(--dur) ease-in-out infinite alternate;
        transform-origin: bottom;
    }
    @keyframes equalize {
        0% { transform: scaleY(0.15); }
        50% { transform: scaleY(1); }
        100% { transform: scaleY(0.3); }
    }

    .episode-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .episode-card:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.02);
    }
    .episode-card:hover .ep-play {
        opacity: 1;
        transform: scale(1);
    }
    .episode-card:hover .ep-number {
        opacity: 0;
    }
    .ep-play {
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.2s ease;
    }
    .ep-number {
        transition: opacity 0.2s ease;
    }

    .subscribe-btn {
        transition: all 0.2s ease;
    }
    .subscribe-btn:hover {
        transform: translateY(-2px);
    }
</style>

{{-- ===== PODCAST HERO ===== --}}
<section class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
    {{-- Ambient glow --}}
    <div class="absolute top-0 left-1/3 w-[700px] h-[700px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, {{ $podcast->color }}, transparent 70%);"></div>
    <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] rounded-full opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, {{ $podcast->color }}, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        {{-- Breadcrumb --}}
        <a href="{{ route('podcast.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-300 transition-colors mb-8 relative z-10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All Podcasts
        </a>

        <div class="flex flex-col md:flex-row items-center gap-10 relative z-10">
            {{-- Artwork with glow --}}
            <div class="flex-shrink-0 relative">
                <div class="artwork-glow absolute top-1/2 left-1/2 w-72 h-72 rounded-full blur-[80px]" style="background: {{ $podcast->color }};"></div>

                @if($podcast->cover_image)
                <img src="{{ asset($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="relative w-48 h-48 md:w-56 md:h-56 rounded-2xl object-cover shadow-2xl ring-1 ring-white/10">
                @else
                <div class="relative w-48 h-48 md:w-56 md:h-56 rounded-2xl shadow-2xl flex items-center justify-center ring-1 ring-white/10" style="background: linear-gradient(135deg, {{ $podcast->color }}44, {{ $podcast->color }}11);">
                    <svg class="w-20 h-20" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 text-center md:text-left">
                {{-- Badge --}}
                <div class="flex items-center gap-3 mb-4 justify-center md:justify-start">
                    @if($episodes->count())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                        {{ $episodes->total() }} {{ Str::plural('Episode', $episodes->total()) }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $podcast->color }};"></span>
                        Coming Soon
                    </span>
                    @endif

                    {{-- Mini equalizer --}}
                    <div class="flex items-end gap-[2px] h-4">
                        <span class="eq-bar w-[3px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.7s;"></span>
                        <span class="eq-bar w-[3px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.5s;"></span>
                        <span class="eq-bar w-[3px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.8s;"></span>
                        <span class="eq-bar w-[3px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.6s;"></span>
                    </div>
                </div>

                <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight text-white">{{ $podcast->name }}</h1>
                <p class="text-gray-400 text-lg leading-relaxed mb-8 max-w-2xl">{{ $podcast->description }}</p>

                {{-- Subscribe buttons --}}
                <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                    @if($podcast->spotify_url)
                    <a href="{{ $podcast->spotify_url }}" target="_blank" class="subscribe-btn inline-flex items-center gap-2 px-5 py-2.5 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-xl hover:bg-[#1DB954]/20 transition-colors border border-[#1DB954]/20">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                        Spotify
                    </a>
                    @endif
                    @if($podcast->apple_url)
                    <a href="{{ $podcast->apple_url }}" target="_blank" class="subscribe-btn inline-flex items-center gap-2 px-5 py-2.5 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-xl hover:bg-[#D56DFB]/20 transition-colors border border-[#D56DFB]/20">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z"/></svg>
                        Apple Podcasts
                    </a>
                    @endif
                    @if($podcast->youtube_url)
                    <a href="{{ $podcast->youtube_url }}" target="_blank" class="subscribe-btn inline-flex items-center gap-2 px-5 py-2.5 bg-red-500/10 text-red-400 text-sm font-medium rounded-xl hover:bg-red-500/20 transition-colors border border-red-500/20">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        YouTube
                    </a>
                    @endif
                    @if($podcast->rss_url)
                    <a href="{{ $podcast->rss_url }}" target="_blank" class="subscribe-btn inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500/10 text-orange-400 text-sm font-medium rounded-xl hover:bg-orange-500/20 transition-colors border border-orange-500/20">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 010 4.36 2.18 2.18 0 010-4.36M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z"/></svg>
                        RSS
                    </a>
                    @endif
                    @unless($podcast->spotify_url || $podcast->apple_url || $podcast->youtube_url || $podcast->rss_url)
                    <span class="inline-flex items-center gap-1.5 px-5 py-2.5 text-sm text-gray-500 border border-[#1e2a3a] rounded-xl">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $podcast->color }};"></span>
                        Subscribe links coming soon
                    </span>
                    @endunless
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== ABOUT ===== --}}
@if($podcast->long_description)
<section class="border-b border-[#1e2a3a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="max-w-3xl">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">About the Show</h2>
            <div class="text-gray-400 text-base leading-relaxed space-y-4">
                @foreach(explode("\n\n", $podcast->long_description) as $paragraph)
                    @if(trim($paragraph))
                    <p>{{ trim($paragraph) }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== LATEST EPISODE FEATURE ===== --}}
@if($latestEpisode)
<section class="border-b border-[#1e2a3a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <a href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}" class="group block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden hover:border-opacity-60 transition-all" style="box-shadow: 0 0 60px {{ $podcast->color }}06;">
            {{-- Top accent --}}
            <div class="h-[2px]" style="background: linear-gradient(90deg, transparent, {{ $podcast->color }}, transparent);"></div>

            <div class="p-8 md:p-10">
                <div class="flex items-center gap-3 mb-5">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">Latest Episode</span>
                    <span class="font-mono text-sm text-gray-500">{{ $latestEpisode->episode_code }}</span>
                    <span class="text-sm text-gray-600">·</span>
                    <span class="text-sm text-gray-500">{{ $latestEpisode->published_at->format('M d, Y') }}</span>
                    @if($latestEpisode->formatted_duration)
                    <span class="text-sm text-gray-600">·</span>
                    <span class="text-sm text-gray-500">{{ $latestEpisode->formatted_duration }}</span>
                    @endif
                </div>

                <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-3 group-hover:opacity-80 transition-opacity">{{ $latestEpisode->title }}</h2>

                @if($latestEpisode->description)
                <p class="text-gray-400 leading-relaxed mb-6 max-w-3xl">{{ Str::limit($latestEpisode->description, 300) }}</p>
                @endif

                @if($latestEpisode->guest_name)
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">{{ substr($latestEpisode->guest_name, 0, 1) }}</div>
                    <div>
                        <span class="text-sm text-gray-300">{{ $latestEpisode->guest_name }}</span>
                        @if($latestEpisode->guest_title)
                        <span class="text-sm text-gray-600"> · {{ $latestEpisode->guest_title }}</span>
                        @endif
                    </div>
                </div>
                @endif

                <div class="inline-flex items-center gap-2 text-sm font-semibold" style="color: {{ $podcast->color }};">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform" style="background: {{ $podcast->color }}; box-shadow: 0 0 20px {{ $podcast->color }}40;">
                        <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    <span>Listen Now</span>
                </div>
            </div>
        </a>
    </div>
</section>
@endif

{{-- ===== ALL EPISODES ===== --}}
<section class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-extrabold text-white">
                @if($episodes->count()) All Episodes @else Episodes @endif
            </h2>
            @if($episodes->total() > 0)
            <span class="text-sm text-gray-500 font-mono">{{ $episodes->total() }} total</span>
            @endif
        </div>

        @if($episodes->count())
        <div class="space-y-2">
            @foreach($episodes as $episode)
            <a href="{{ route('podcast.episode', [$podcast, $episode]) }}" class="episode-card group flex items-center gap-5 p-4 md:p-5 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                {{-- Episode number / play icon --}}
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center relative" style="background: {{ $podcast->color }}08;">
                    <span class="ep-number font-mono text-xs font-bold" style="color: {{ $podcast->color }};">{{ $episode->episode_code }}</span>
                    <div class="ep-play absolute inset-0 flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: {{ $podcast->color }};">
                            <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-white group-hover:opacity-80 transition-opacity truncate">{{ $episode->title }}</h3>
                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                        <span>{{ $episode->published_at->format('M d, Y') }}</span>
                        @if($episode->formatted_duration)
                        <span class="text-gray-700">·</span>
                        <span>{{ $episode->formatted_duration }}</span>
                        @endif
                        @if($episode->guest_name)
                        <span class="hidden sm:inline text-gray-700">·</span>
                        <span class="hidden sm:inline">with <span style="color: {{ $podcast->color }};">{{ $episode->guest_name }}</span></span>
                        @endif
                    </div>
                </div>

                {{-- Mini equalizer on hover --}}
                <div class="hidden md:flex items-end gap-[2px] h-5 opacity-0 group-hover:opacity-60 transition-opacity">
                    <span class="eq-bar w-[2px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.6s;"></span>
                    <span class="eq-bar w-[2px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.8s;"></span>
                    <span class="eq-bar w-[2px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.5s;"></span>
                    <span class="eq-bar w-[2px] h-full rounded-full" style="background: {{ $podcast->color }}; --dur: 0.7s;"></span>
                </div>

                <svg class="w-5 h-5 text-gray-600 group-hover:translate-x-1 transition-transform flex-shrink-0 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $episodes->links() }}
        </div>
        @else
        <div class="text-center py-20 rounded-2xl border border-dashed border-[#1e2a3a]">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: {{ $podcast->color }}10;">
                <svg class="w-8 h-8" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
            </div>
            <p class="text-gray-400 text-lg font-medium mb-2">No episodes yet</p>
            <p class="text-gray-500 text-sm">First episodes are in the works. Check back soon!</p>
        </div>
        @endif
    </div>
</section>
@endsection
