@extends('layouts.app')

@section('title', $podcast->name)

@section('content')
    {{-- Podcast Header --}}
    <div class="border-b border-brand-800/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row items-start gap-8">
                @if($podcast->cover_image)
                <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-40 h-40 rounded-2xl object-cover flex-shrink-0">
                @else
                <div class="w-40 h-40 rounded-2xl bg-brand-800 flex items-center justify-center flex-shrink-0">
                    <svg class="w-16 h-16 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z"/></svg>
                </div>
                @endif
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $podcast->name }}</h1>
                    <p class="text-gray-400 text-lg mb-4 max-w-2xl">{{ $podcast->description }}</p>
                    <div class="flex flex-wrap gap-3">
                        @if($podcast->spotify_url)
                        <a href="{{ $podcast->spotify_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-lg hover:bg-[#1DB954]/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                            Spotify
                        </a>
                        @endif
                        @if($podcast->apple_podcasts_url)
                        <a href="{{ $podcast->apple_podcasts_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-lg hover:bg-[#D56DFB]/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34zm6.525 2.568c2.336 0 4.448.902 6.025 2.392a8.07 8.07 0 012.497 5.173 1.053 1.053 0 01-2.095.168 5.994 5.994 0 00-1.855-3.84 5.93 5.93 0 00-4.572-1.78 5.988 5.988 0 00-5.552 4.26 1.053 1.053 0 01-2.03-.558 8.088 8.088 0 017.582-5.815zm.098 3.108a4.7 4.7 0 013.695 1.705 4.67 4.67 0 011.17 3.392 1.053 1.053 0 01-2.1-.09 2.593 2.593 0 00-.65-1.883 2.607 2.607 0 00-2.048-.944 2.6 2.6 0 00-2.56 2.163 1.053 1.053 0 01-2.074-.37 4.706 4.706 0 014.567-3.973zm-.228 4.392c.68 0 1.303.27 1.757.713.455.442.72 1.052.72 1.727 0 .457-.136.912-.393 1.378-.264.478-.6.87-.862 1.142l-.105.112-1.182 2.947a1.018 1.018 0 01-1.872 0l-1.182-2.947-.105-.112c-.262-.272-.598-.664-.862-1.142-.257-.466-.393-.921-.393-1.378 0-.675.265-1.285.72-1.727a2.49 2.49 0 011.759-.713z"/></svg>
                            Apple Podcasts
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Episodes --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($latestEpisode)
        {{-- Featured Latest Episode --}}
        <div class="mb-12 p-6 rounded-2xl border border-brand-600/30 bg-brand-900/50">
            <span class="text-xs font-semibold text-brand-400 uppercase tracking-wide">Latest Episode</span>
            <h2 class="text-2xl font-bold mt-2 mb-1">
                <a href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}" class="hover:text-brand-400 transition-colors">{{ $latestEpisode->title }}</a>
            </h2>
            <div class="flex items-center gap-3 text-sm text-gray-500 mb-3">
                <span class="font-mono text-brand-400">{{ $latestEpisode->episode_code }}</span>
                <span>{{ $latestEpisode->published_at->format('M d, Y') }}</span>
                @if($latestEpisode->formatted_duration)
                <span>{{ $latestEpisode->formatted_duration }}</span>
                @endif
                @if($latestEpisode->guest_name)
                <span>with {{ $latestEpisode->guest_name }}</span>
                @endif
            </div>
            <p class="text-gray-400 text-sm mb-4">{{ $latestEpisode->excerpt }}</p>
            @if($latestEpisode->audio_url)
            <audio controls preload="none" class="w-full max-w-xl rounded-lg">
                <source src="{{ $latestEpisode->audio_url }}" type="audio/mpeg">
            </audio>
            @endif
        </div>
        @endif

        <h2 class="text-xl font-semibold mb-6">All Episodes</h2>
        <div class="space-y-4">
            @forelse($episodes as $episode)
            <a href="{{ route('podcast.episode', [$podcast, $episode]) }}" class="group flex items-center gap-5 p-4 rounded-xl hover:bg-brand-900/50 transition-colors">
                <div class="flex-shrink-0 w-14 h-14 rounded-lg bg-brand-800 flex items-center justify-center">
                    <span class="font-mono text-sm text-brand-400 font-medium">{{ $episode->episode_code }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold group-hover:text-brand-400 transition-colors truncate">{{ $episode->title }}</h3>
                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                        <span>{{ $episode->published_at->format('M d, Y') }}</span>
                        @if($episode->formatted_duration)
                        <span>{{ $episode->formatted_duration }}</span>
                        @endif
                        @if($episode->guest_name)
                        <span class="text-accent-400">with {{ $episode->guest_name }}</span>
                        @endif
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-600 group-hover:text-brand-400 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @empty
            <div class="text-center py-20 text-gray-500">
                <p class="text-lg">No episodes published yet.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $episodes->links() }}
        </div>
    </div>
@endsection
