@extends('layouts.app')

@section('title', 'Podcasts')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-2">Podcasts</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-12">Two shows, two passions. Tune in wherever you listen.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($podcasts as $podcast)
            <a href="{{ route('podcast.show', $podcast) }}" class="group block rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden hover:border-gray-300 dark:hover:border-gray-700 transition-all hover:shadow-xl">
                {{-- Cover --}}
                <div class="aspect-[2/1] overflow-hidden" style="background-color: {{ $podcast->color }}20">
                    @if($podcast->cover_image)
                    <img src="{{ Storage::url($podcast->cover_image) }}" alt="{{ $podcast->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-6xl">
                        @if(str_contains(strtolower($podcast->name), 'coffee')) ☕ @else 🌧️ @endif
                    </div>
                    @endif
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $podcast->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $podcast->description }}</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>{{ $podcast->published_episodes_count }} {{ Str::plural('episode', $podcast->published_episodes_count) }}</span>
                        @if($podcast->apple_url) <span>Apple</span> @endif
                        @if($podcast->spotify_url) <span>Spotify</span> @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
@endsection
