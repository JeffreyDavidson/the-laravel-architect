@extends('layouts.app')

@section('title', 'Podcast')

@section('content')
{{-- ===== HERO ===== --}}
<div class="noise-overlay podcast-page-border relative overflow-hidden border-b bg-white dark:bg-transparent">
    <div class="podcast-orb-brand absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]"></div>
    <div class="podcast-orb-brand absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-0 dark:opacity-[0.04] blur-[100px]"></div>

    {{-- Waveform background --}}
    <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] dark:opacity-[0.04] pointer-events-none">
        <div class="flex items-end gap-[3px] h-32 w-full max-w-4xl px-8">
            @for($i = 0; $i < 80; $i++)
            <div class="podcast-wave-bar flex-1 rounded-full" style="--dur: {{ 0.8 + ($i % 7) * 0.15 }}s; --delay: {{ $i * 0.03 }}s;"></div>
            @endfor
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <x-terminal-prompt command="podcast:episode-list" />

        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">Podcast</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">Coffee with The Laravel Architect is where Laravel, architecture, and the developer life meet.</p>

        {{-- Stats with equalizer --}}
        <div class="flex items-center gap-6 mt-6 text-sm">
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="podcast-brand-text w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                <span>{{ $podcast ? '1 Show' : 'No Show Yet' }}</span>
            </div>
            <div class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></div>
            <div class="flex items-center gap-2 text-gray-500">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span>Launching Soon</span>
            </div>
            <div class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-700"></div>
            <div class="inline-flex items-end gap-[2px] h-4">
                <span class="podcast-eq-bar podcast-brand-bg w-[3px] h-full rounded-full" style="--dur: 0.8s;"></span>
                <span class="podcast-eq-bar podcast-brand-bg w-[3px] h-full rounded-full" style="--dur: 0.6s;"></span>
                <span class="podcast-eq-bar podcast-brand-bg w-[3px] h-full rounded-full" style="--dur: 0.9s;"></span>
                <span class="podcast-eq-bar podcast-brand-bg w-[3px] h-full rounded-full" style="--dur: 0.7s;"></span>
                <span class="podcast-eq-bar podcast-brand-bg w-[3px] h-full rounded-full" style="--dur: 0.5s;"></span>
            </div>
        </div>
    </div>
</div>

{{-- ===== PODCAST SHOWCASES ===== --}}
<section class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        @if($podcast)
        <div class="space-y-10">
            <a href="{{ route('podcast.show', $podcast) }}" class="podcast-showcase podcast-surface group relative block rounded-2xl border overflow-hidden" style="--podcast-color: {{ $podcast->color }};">
                {{-- Glow --}}
                <div class="podcast-showcase-glow showcase-glow absolute inset-0 rounded-2xl opacity-0"></div>

                {{-- Top gradient bar --}}
                <div class="podcast-showcase-rule h-[2px] w-full"></div>

                <div class="relative p-8 md:p-12">
                    <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">

                        {{-- Artwork with vinyl record effect --}}
                        <div class="relative flex-shrink-0 w-48 h-48 md:w-60 md:h-60">
                            {{-- Vinyl disc behind artwork --}}
                            <div class="podcast-vinyl absolute top-1/2 left-1/2 -translate-x-[30%] -translate-y-1/2 w-44 h-44 md:w-52 md:h-52 rounded-full border vinyl-spin">
                                <div class="podcast-vinyl-grooves absolute inset-0 rounded-full"></div>
                                <div class="podcast-vinyl-center absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-12 h-12 rounded-full"></div>
                            </div>

                            {{-- Cover art --}}
                            <div class="showcase-artwork relative z-10">
                                @if($podcast->cover_image_url)
                                <img src="{{ $podcast->cover_image_url }}" alt="{{ $podcast->name }}" class="podcast-cover-shadow w-40 h-40 md:w-48 md:h-48 rounded-2xl object-cover shadow-2xl">
                                @else
                                <div class="podcast-cover-placeholder w-40 h-40 md:w-48 md:h-48 rounded-2xl shadow-2xl flex items-center justify-center">
                                    <svg class="podcast-accent-text w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0 text-center md:text-left">
                            {{-- Badge --}}
                            @if($podcast->published_episodes_count > 0)
                            <span class="podcast-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-4">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                {{ $podcast->published_episodes_count }} {{ Str::plural('Episode', $podcast->published_episodes_count) }}
                            </span>
                            @else
                            <span class="podcast-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide mb-4">
                            <span class="podcast-live-dot w-1.5 h-1.5 rounded-full"></span>
                                Coming Soon
                            </span>
                            @endif

                            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-gray-900 dark:text-white transition-colors">{{ $podcast->name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-base md:text-lg leading-relaxed mb-6 max-w-xl">{{ $podcast->description }}</p>

                            {{-- Topic pills --}}
                            <div class="flex flex-wrap gap-2 justify-center md:justify-start mb-6">
                                @php($topics = ['Laravel', 'Architecture', 'Testing', 'Career', 'Guest Interviews'])
                                @foreach($topics as $topic)
                                <span class="topic-pill px-3 py-1 text-xs rounded-full border">{{ $topic }}</span>
                                @endforeach
                            </div>

                            {{-- Listen CTA --}}
                            <div class="flex justify-center md:justify-start">
                                <div class="podcast-accent-text inline-flex items-center gap-2 text-sm font-semibold">
                                    <span>View Show</span>
                                    <svg class="w-4 h-4 showcase-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @else
        <div class="text-center py-20">
            <div class="inline-flex items-end gap-[3px] mb-4 h-8 opacity-30">
                @for($i = 0; $i < 5; $i++)
                <span class="podcast-eq-bar w-[3px] h-full bg-gray-500 rounded-full" style="--dur: {{ 0.5 + $i * 0.1 }}s;"></span>
                @endfor
            </div>
            <p class="text-gray-500 text-lg">Podcast launching soon. Stay tuned.</p>
        </div>
        @endif
    </div>
</section>

{{-- ===== WHAT TO EXPECT ===== --}}
@if($podcast)
<section class="podcast-page-border relative border-t overflow-hidden">
    {{-- Floating orbs --}}
    <div class="podcast-float podcast-brand-bg absolute top-20 left-10 w-32 h-32 rounded-full opacity-[0.04] blur-[60px]"></div>
    <div class="podcast-float-reverse podcast-brand-bg absolute bottom-20 right-10 w-40 h-40 rounded-full opacity-[0.04] blur-[60px]"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <div class="text-center mb-14">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-600 mb-3">What You'll Get</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">Every Episode, Every Conversation</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Format card 1 --}}
            <div class="format-card podcast-surface rounded-2xl border p-8 text-center">
                <div class="format-icon podcast-brand-icon w-14 h-14 mx-auto mb-5 rounded-2xl flex items-center justify-center">
                    <svg class="podcast-brand-text w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-gray-900 dark:text-white">Architecture Deep Dives</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Real-world Laravel patterns, testing strategies, and the decisions behind production code. No toy examples.</p>
            </div>

            {{-- Format card 2 --}}
            <div class="format-card podcast-surface rounded-2xl border p-8 text-center">
                <div class="format-icon w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 flex items-center justify-center">
                    <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-gray-900 dark:text-white">Practical Takeaways</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Clear explanations, tradeoffs, and decisions you can use in real Laravel projects without sitting through filler.</p>
            </div>

            {{-- Format card 3 --}}
            <div class="format-card podcast-surface rounded-2xl border p-8 text-center">
                <div class="format-icon podcast-brand-icon w-14 h-14 mx-auto mb-5 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-3 text-gray-900 dark:text-white">Guest Conversations</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">Developers, creators, and thinkers sharing war stories, mistakes, and the lessons they carry forward.</p>
            </div>
        </div>
    </div>
</section>
@endif

@endsection
