@extends('layouts.app')

@section('title', 'Podcasts')

@section('content')
    {{-- Hero --}}
    <div class="relative overflow-hidden border-b border-brand-800/50">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-600/10 via-transparent to-accent-500/10"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-800/50 text-brand-400 text-xs font-semibold tracking-wide uppercase mb-6">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                Podcasts
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Coffee with the <span class="text-brand-400">Laravel Architect</span></h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">Deep dives into Laravel, PHP, architecture patterns, and conversations with the people building the modern web.</p>
        </div>
    </div>

    {{-- Podcast List --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($podcasts as $podcast)
            <a href="{{ route('podcast.show', $podcast) }}" class="group block p-6 rounded-2xl border border-brand-800/50 bg-brand-900/30 hover:border-brand-600/50 transition-all">
                <div class="flex items-start gap-5">
                    @if($podcast->cover_image)
                    <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-24 h-24 rounded-xl object-cover flex-shrink-0">
                    @else
                    <div class="w-24 h-24 rounded-xl bg-brand-800 flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-semibold group-hover:text-brand-400 transition-colors mb-2">{{ $podcast->name }}</h2>
                        <p class="text-gray-400 text-sm line-clamp-2 mb-3">{{ $podcast->description }}</p>
                        <span class="text-xs text-gray-500">{{ $podcast->published_episodes_count }} {{ Str::plural('episode', $podcast->published_episodes_count) }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-20 text-gray-500">
                <p class="text-lg">No podcasts yet. Stay tuned!</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection
