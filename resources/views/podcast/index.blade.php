@extends('layouts.app')

@section('title', 'Podcasts')

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
    .podcast-card {
        transition: all 0.3s ease;
    }
    .podcast-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
    }
    .podcast-card:hover .podcast-glow {
        opacity: 1;
    }
    .podcast-glow {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .equalizer-bar {
        animation: equalize var(--dur) ease-in-out infinite alternate;
        transform-origin: bottom;
    }
    @keyframes equalize {
        0% { transform: scaleY(0.2); }
        50% { transform: scaleY(0.8); }
        100% { transform: scaleY(0.4); }
    }
</style>

    {{-- Hero --}}
    <div class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
        <div class="absolute inset-0 bg-gradient-to-br from-[#4A7FBF]/8 via-transparent to-[#9D5175]/8"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 text-center">
            {{-- Equalizer icon --}}
            <div class="inline-flex items-end gap-[3px] mb-6 h-6">
                <span class="equalizer-bar w-[3px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.8s;"></span>
                <span class="equalizer-bar w-[3px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.6s;"></span>
                <span class="equalizer-bar w-[3px] h-full bg-[#9D5175] rounded-full" style="--dur: 0.9s;"></span>
                <span class="equalizer-bar w-[3px] h-full bg-[#4A7FBF] rounded-full" style="--dur: 0.7s;"></span>
                <span class="equalizer-bar w-[3px] h-full bg-[#9D5175] rounded-full" style="--dur: 0.5s;"></span>
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 tracking-tight">Podcasts</h1>
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto">Two shows. One about code, one about life. Both unfiltered.</p>
        </div>
    </div>

    {{-- Podcast Cards --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            @if($podcasts->count())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($podcasts as $podcast)
                <a href="{{ route('podcast.show', $podcast) }}" class="podcast-card group relative block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden">
                    {{-- Glow effect --}}
                    <div class="podcast-glow absolute inset-0 rounded-2xl" style="box-shadow: inset 0 0 60px {{ $podcast->color }}15, 0 0 30px {{ $podcast->color }}10;"></div>

                    {{-- Top accent bar --}}
                    <div class="h-1 w-full" style="background: linear-gradient(90deg, {{ $podcast->color }}, {{ $podcast->color }}66);"></div>

                    <div class="relative p-8">
                        <div class="flex items-start gap-6">
                            {{-- Podcast artwork --}}
                            @if($podcast->cover_image)
                            <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-28 h-28 rounded-xl object-cover flex-shrink-0 shadow-lg">
                            @else
                            <div class="w-28 h-28 rounded-xl flex-shrink-0 shadow-lg flex items-center justify-center" style="background: linear-gradient(135deg, {{ $podcast->color }}33, {{ $podcast->color }}11);">
                                <svg class="w-12 h-12" style="color: {{ $podcast->color }};" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                            </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h2 class="text-2xl font-bold mb-2 group-hover:text-white transition-colors" style="color: {{ $podcast->color }};">{{ $podcast->name }}</h2>
                                <p class="text-gray-400 text-sm leading-relaxed mb-4">{{ $podcast->description }}</p>

                                @if($podcast->published_episodes_count > 0)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                    {{ $podcast->published_episodes_count }} {{ Str::plural('episode', $podcast->published_episodes_count) }}
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase" style="background: {{ $podcast->color }}15; color: {{ $podcast->color }};">
                                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: {{ $podcast->color }};"></span>
                                    Coming Soon
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="absolute bottom-8 right-8">
                            <svg class="w-5 h-5 text-gray-600 group-hover:translate-x-1 transition-transform" style="--tw-translate-x: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-20">
                <div class="inline-flex items-end gap-[3px] mb-4 h-8 opacity-30">
                    <span class="equalizer-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: 0.8s;"></span>
                    <span class="equalizer-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: 0.6s;"></span>
                    <span class="equalizer-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: 0.9s;"></span>
                    <span class="equalizer-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: 0.7s;"></span>
                    <span class="equalizer-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: 0.5s;"></span>
                </div>
                <p class="text-gray-500 text-lg">Podcasts launching soon. Stay tuned.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- What to Expect Section --}}
    @if($podcasts->count())
    <div class="border-t border-[#1e2a3a]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
            <h2 class="text-2xl font-bold mb-8 text-center">What to Expect</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <h3 class="font-semibold mb-2">Architecture Deep Dives</h3>
                    <p class="text-sm text-gray-400">Breaking down real-world Laravel patterns, testing strategies, and the decisions behind the code.</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-[#9D5175]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#9D5175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <h3 class="font-semibold mb-2">Real Talk on Mental Health</h3>
                    <p class="text-sm text-gray-400">Honest conversations about the hard days — burnout, parenting, and finding strength in vulnerability.</p>
                </div>
                <div class="text-center p-6">
                    <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="font-semibold mb-2">Guest Conversations</h3>
                    <p class="text-sm text-gray-400">Developers, creators, and thinkers sharing their stories, mistakes, and hard-won lessons.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection
