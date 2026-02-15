@extends('layouts.app')

@section('title', 'Podcasts')

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

    /* Waveform animation */
    @keyframes waveform {
        0%, 100% { height: 20%; }
        25% { height: 80%; }
        50% { height: 40%; }
        75% { height: 90%; }
    }
    .wave-bar {
        animation: waveform var(--dur, 1.2s) ease-in-out infinite;
        animation-delay: var(--delay, 0s);
    }

    /* Equalizer */
    .eq-bar {
        animation: equalize var(--dur) ease-in-out infinite alternate;
        transform-origin: bottom;
    }
    @keyframes equalize {
        0% { transform: scaleY(0.15); }
        50% { transform: scaleY(1); }
        100% { transform: scaleY(0.3); }
    }

    /* Podcast showcase card */
    .podcast-showcase {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .podcast-showcase:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5);
    }
    .podcast-showcase:hover .showcase-glow {
        opacity: 1;
    }
    .podcast-showcase:hover .showcase-artwork {
        transform: scale(1.05) rotate(-1deg);
    }
    .podcast-showcase:hover .showcase-arrow {
        transform: translateX(4px);
    }
    .showcase-artwork {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .showcase-arrow {
        transition: transform 0.3s ease;
    }

    /* Spinning vinyl */
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .vinyl-spin {
        animation: spin-slow 8s linear infinite;
    }
    .podcast-showcase:hover .vinyl-spin {
        animation-duration: 3s;
    }

    /* Topic pill hover */
    .topic-pill {
        transition: all 0.2s ease;
    }
    .topic-pill:hover {
        transform: translateY(-1px);
    }

    /* Format card */
    .format-card {
        transition: all 0.3s ease;
    }
    .format-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255,255,255,0.1);
    }
    .format-card:hover .format-icon {
        transform: scale(1.1);
    }
    .format-icon {
        transition: transform 0.3s ease;
    }

    /* Floating orbs */
    @keyframes float {
        0%, 100% { transform: translate(0, 0); }
        25% { transform: translate(10px, -15px); }
        50% { transform: translate(-5px, -25px); }
        75% { transform: translate(-15px, -10px); }
    }
</style>

{{-- ===== HERO ===== --}}
<section class="noise-overlay relative overflow-hidden min-h-[60vh] flex items-center border-b border-[#1e2a3a]">
    {{-- Background gradients --}}
    <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
        <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>
    </div>

    {{-- Waveform background --}}
    <div class="absolute inset-0 flex items-center justify-center opacity-[0.04] pointer-events-none">
        <div class="flex items-end gap-[3px] h-40 w-full max-w-4xl px-8">
            @for($i = 0; $i < 80; $i++)
            <div class="wave-bar flex-1 rounded-full" style="--dur: {{ 0.8 + ($i % 7) * 0.15 }}s; --delay: {{ $i * 0.03 }}s; background: linear-gradient(to top, #4A7FBF, #9D5175);"></div>
            @endfor
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center w-full">
        {{-- Live equalizer --}}
        <div class="inline-flex items-end gap-[3px] mb-8 h-8">
            <span class="eq-bar w-[4px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.8s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.6s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#9D5175] rounded-full" style="--dur: 0.9s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#9D5175] rounded-full" style="--dur: 0.7s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.5s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 1.0s;"></span>
            <span class="eq-bar w-[4px] h-full bg-[#9D5175] rounded-full" style="--dur: 0.65s;"></span>
        </div>

        <h1 class="text-5xl md:text-7xl font-extrabold mb-5 tracking-tight">
            <span class="bg-gradient-to-r from-white via-white to-gray-400 bg-clip-text text-transparent">Podcasts</span>
        </h1>
        <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto mb-8">Two shows. One about code, one about life. Both unfiltered.</p>

        {{-- Stats --}}
        <div class="flex items-center justify-center gap-8 text-sm">
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="w-4 h-4 text-[#4A7FBF]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                <span>{{ $podcasts->count() }} Shows</span>
            </div>
            <div class="w-1 h-1 rounded-full bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span>Launching Soon</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== PODCAST SHOWCASES ===== --}}
<section class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        @if($podcasts->count())
        <div class="space-y-16">
            @foreach($podcasts as $index => $podcast)
            <a href="{{ route('podcast.show', $podcast) }}" class="podcast-showcase group relative block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden">
                {{-- Glow --}}
                <div class="showcase-glow absolute inset-0 rounded-2xl opacity-0" style="box-shadow: inset 0 0 80px {{ $podcast->color }}12, 0 0 60px {{ $podcast->color }}08;"></div>

                {{-- Top gradient bar --}}
                <div class="h-[2px] w-full" style="background: linear-gradient(90deg, transparent, {{ $podcast->color }}, transparent);"></div>

                <div class="relative p-8 md:p-12">
                    <div class="flex flex-col {{ $index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse' }} items-center gap-10">

                        {{-- Artwork with vinyl record effect --}}
                        <div class="relative flex-shrink-0">
                            {{-- Vinyl disc behind artwork --}}
                            <div class="absolute top-1/2 {{ $index % 2 === 0 ? 'left-1/2 -translate-x-[30%]' : 'right-1/2 translate-x-[30%]' }} -translate-y-1/2 w-44 h-44 md:w-52 md:h-52 rounded-full bg-[#111] border border-[#222] vinyl-spin">
                                <div class="absolute inset-0 rounded-full" style="background: repeating-radial-gradient(circle at center, transparent 0px, transparent 8px, rgba(255,255,255,0.02) 8px, rgba(255,255,255,0.02) 9px);"></div>
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 rounded-full" style="background: {{ $podcast->color }}33; border: 2px solid {{ $podcast->color }}44;"></div>
                            </div>

                            {{-- Cover art --}}
                            <div class="showcase-artwork relative z-10">
                                @if($podcast->cover_image)
                                <img src="{{ asset($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-40 h-40 md:w-48 md:h-48 rounded-2xl object-cover shadow-2xl" style="box-shadow: 0 20px 40px {{ $podcast->color }}20;">
                                @else
                                <div class="w-40 h-40 md:w-48 md:h-48 rounded-2xl shadow-2xl flex items-center justify-center" style="background: linear-gradient(135deg, {{ $podcast->color }}44, {{ $podcast->color }}11); box-shadow: 0 20px 40px {{ $podcast->color }}20;">
                                    <svg class="w-16 h-16" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0 {{ $index % 2 === 0 ? 'text-left' : 'md:text-right' }} text-center md:text-left">
                            {{-- Badge --}}
                            @if($podcast->published_episodes_count > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-4" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                {{ $podcast->published_episodes_count }} {{ Str::plural('Episode', $podcast->published_episodes_count) }}
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">
                                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $podcast->color }};"></span>
                                Coming Soon
                            </span>
                            @endif

                            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-white group-hover:text-white/90 transition-colors">{{ $podcast->name }}</h2>
                            <p class="text-gray-400 text-base md:text-lg leading-relaxed mb-6 max-w-xl {{ $index % 2 !== 0 ? 'md:ml-auto' : '' }}">{{ $podcast->description }}</p>

                            {{-- Topic pills --}}
                            @php
                            $topics = $index === 0
                                ? ['Laravel', 'Architecture', 'Testing', 'Career', 'Guest Interviews']
                                : ['Mental Health', 'Parenting', 'Resilience', 'Vulnerability', 'Real Talk'];
                            @endphp
                            <div class="flex flex-wrap gap-2 {{ $index % 2 !== 0 ? 'md:justify-end' : '' }} justify-center md:justify-start mb-6">
                                @foreach($topics as $topic)
                                <span class="topic-pill px-3 py-1 text-xs rounded-full border" style="border-color: {{ $podcast->color }}25; color: {{ $podcast->color }}; background: {{ $podcast->color }}08;">{{ $topic }}</span>
                                @endforeach
                            </div>

                            {{-- Listen CTA --}}
                            <div class="inline-flex items-center gap-2 text-sm font-semibold" style="color: {{ $podcast->color }};">
                                <span>View Show</span>
                                <svg class="w-4 h-4 showcase-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-20">
            <div class="inline-flex items-end gap-[3px] mb-4 h-8 opacity-30">
                @for($i = 0; $i < 5; $i++)
                <span class="eq-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: {{ 0.5 + $i * 0.1 }}s;"></span>
                @endfor
            </div>
            <p class="text-gray-500 text-lg">Podcasts launching soon. Stay tuned.</p>
        </div>
        @endif
    </div>
</section>

{{-- ===== WHAT TO EXPECT ===== --}}
@if($podcasts->count())
<section class="relative border-t border-[#1e2a3a] overflow-hidden">
    {{-- Floating orbs --}}
    <div class="absolute top-20 left-10 w-32 h-32 rounded-full opacity-[0.04] blur-[60px] bg-[#4A7FBF]" style="animation: float 8s ease-in-out infinite;"></div>
    <div class="absolute bottom-20 right-10 w-40 h-40 rounded-full opacity-[0.04] blur-[60px] bg-[#9D5175]" style="animation: float 10s ease-in-out infinite reverse;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="text-center mb-14">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">What You'll Get</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white">Every Episode, Every Show</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Format card 1 --}}
            <div class="format-card rounded-2xl border border-[#1e2a3a] bg-[#0D1117] p-8 text-center">
                <div class="format-icon w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-[#4A7FBF]/20 to-[#4A7FBF]/5 flex items-center justify-center">
                    <svg class="w-7 h-7 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-white">Architecture Deep Dives</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Real-world Laravel patterns, testing strategies, and the decisions behind production code. No toy examples.</p>
            </div>

            {{-- Format card 2 --}}
            <div class="format-card rounded-2xl border border-[#1e2a3a] bg-[#0D1117] p-8 text-center">
                <div class="format-icon w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-[#9D5175]/20 to-[#9D5175]/5 flex items-center justify-center">
                    <svg class="w-7 h-7 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-white">Real Talk on Hard Days</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Honest conversations about burnout, parenting, and finding strength when everything feels heavy. No performance.</p>
            </div>

            {{-- Format card 3 --}}
            <div class="format-card rounded-2xl border border-[#1e2a3a] bg-[#0D1117] p-8 text-center">
                <div class="format-icon w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-[#4A7FBF]/20 to-[#9D5175]/5 flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-white">Guest Conversations</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Developers, creators, and thinkers sharing war stories, mistakes, and the lessons they carry forward.</p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ===== SUBSCRIBE CTA ===== --}}
<section class="relative border-t border-[#1e2a3a] overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#4A7FBF]/[0.03] to-transparent"></div>

    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
        {{-- Microphone icon --}}
        <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gradient-to-br from-[#4A7FBF]/20 to-[#9D5175]/20 flex items-center justify-center border border-white/5">
            <svg class="w-8 h-8 text-white/60" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
        </div>

        <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Don't Miss an Episode</h2>
        <p class="text-gray-400 text-lg mb-8 max-w-lg mx-auto">Both shows are launching soon. Subscribe to the newsletter and be the first to know when episodes drop.</p>

        <a href="/#newsletter" class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-white text-[#0D1117] font-bold text-sm hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Get Notified
        </a>
    </div>
</section>
@endsection
