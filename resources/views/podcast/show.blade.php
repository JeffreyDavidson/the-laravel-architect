@extends('layouts.app')

@section('title', $podcast->name)

@section('content')
    {{-- Podcast Hero --}}
    <section style="background: linear-gradient(to bottom, {{ $podcast->color }}15, transparent)">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex flex-col md:flex-row items-center gap-10">
                <div class="w-48 h-48 rounded-2xl overflow-hidden flex-shrink-0 shadow-lg" style="background-color: {{ $podcast->color }}20">
                    @if($podcast->cover_image)
                    <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-7xl">
                        @if(str_contains(strtolower($podcast->name), 'coffee')) ☕ @else 🌧️ @endif
                    </div>
                    @endif
                </div>
                <div>
                    <a href="{{ route('podcast.index') }}" class="text-sm text-gray-500 hover:underline mb-2 inline-block">← All Podcasts</a>
                    <h1 class="text-3xl sm:text-4xl font-bold mb-3">{{ $podcast->name }}</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">{{ $podcast->description }}</p>
                    @if($podcast->long_description)
                    <p class="text-sm text-gray-500 mb-6">{{ $podcast->long_description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-3">
                        @if($podcast->apple_url)
                        <a href="{{ $podcast->apple_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                            🎧 Apple Podcasts
                        </a>
                        @endif
                        @if($podcast->spotify_url)
                        <a href="{{ $podcast->spotify_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            🎧 Spotify
                        </a>
                        @endif
                        @if($podcast->youtube_url)
                        <a href="{{ $podcast->youtube_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            ▶️ YouTube
                        </a>
                        @endif
                        @if($podcast->rss_url)
                        <a href="{{ $podcast->rss_url }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            📡 RSS
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Latest Episode --}}
    @if($latestEpisode)
    <section class="border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-sm font-semibold uppercase tracking-wide mb-4" style="color: {{ $podcast->color }}">Latest Episode</h2>
            <a href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}" class="group block">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs font-mono text-gray-500">{{ $latestEpisode->episode_code }}</span>
                    @if($latestEpisode->guest_name)
                    <span class="text-xs px-2 py-0.5 rounded" style="background-color: {{ $podcast->color }}15; color: {{ $podcast->color }}">with {{ $latestEpisode->guest_name }}</span>
                    @endif
                </div>
                <h3 class="text-2xl font-bold mb-3 group-hover:opacity-80 transition-opacity">{{ $latestEpisode->title }}</h3>
                <p class="text-gray-600 dark:text-gray-400 line-clamp-3 mb-4">{{ $latestEpisode->description }}</p>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <time>{{ $latestEpisode->published_at->format('M d, Y') }}</time>
                    @if($latestEpisode->duration_minutes)
                    <span>{{ $latestEpisode->formatted_duration }}</span>
                    @endif
                </div>
            </a>
        </div>
    </section>
    @endif

    {{-- All Episodes --}}
    <section>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-xl font-bold mb-8">All Episodes</h2>
            <div class="space-y-4">
                @forelse($episodes as $episode)
                <a href="{{ route('podcast.episode', [$podcast, $episode]) }}" class="group flex items-start gap-5 p-4 -mx-4 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center text-sm font-mono font-bold" style="background-color: {{ $podcast->color }}15; color: {{ $podcast->color }}">
                        {{ $episode->episode_number ?? '#' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold group-hover:opacity-80 transition-opacity">{{ $episode->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mt-1">{{ $episode->description }}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                            <time>{{ $episode->published_at->format('M d, Y') }}</time>
                            @if($episode->duration_minutes)
                            <span>{{ $episode->formatted_duration }}</span>
                            @endif
                            @if($episode->guest_name)
                            <span>with {{ $episode->guest_name }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center py-16 text-gray-500">
                    <p class="text-4xl mb-4">🎙️</p>
                    <p class="text-lg">First episode coming soon... stay tuned!</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $episodes->links() }}</div>
        </div>
    </section>
@endsection
