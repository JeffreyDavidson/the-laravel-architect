@extends('layouts.app')

@section('title', $episode->title . ' — ' . $podcast->name)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Header --}}
        <header class="mb-8">
            <a href="{{ route('podcast.show', $podcast) }}" class="text-sm hover:underline mb-4 inline-block" style="color: {{ $podcast->color }}">← {{ $podcast->name }}</a>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-mono text-gray-500">{{ $episode->episode_code }}</span>
                @if($episode->guest_name)
                <span class="text-sm px-2 py-0.5 rounded" style="background-color: {{ $podcast->color }}15; color: {{ $podcast->color }}">with {{ $episode->guest_name }}</span>
                @endif
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">{{ $episode->title }}</h1>
            <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                <time>{{ $episode->published_at->format('F d, Y') }}</time>
                @if($episode->duration_minutes)
                <span>{{ $episode->formatted_duration }}</span>
                @endif
            </div>
            <p class="text-lg text-gray-600 dark:text-gray-400">{{ $episode->description }}</p>
        </header>

        {{-- Audio Player --}}
        @if($episode->audio_url || $episode->audio_file)
        <div class="rounded-xl p-6 mb-8" style="background-color: {{ $podcast->color }}08">
            <audio controls class="w-full" preload="metadata">
                <source src="{{ $episode->audio_url ?? Storage::url($episode->audio_file) }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
        @endif

        {{-- Embed --}}
        @if($episode->embed_url)
        <div class="mb-8">
            <iframe src="{{ $episode->embed_url }}" width="100%" height="232" frameBorder="0" allowfullscreen allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" class="rounded-xl"></iframe>
        </div>
        @endif

        {{-- YouTube --}}
        @if($episode->youtube_url)
        <div class="aspect-video rounded-xl overflow-hidden mb-8 bg-gray-100 dark:bg-gray-800">
            <iframe src="{{ Str::replace('watch?v=', 'embed/', $episode->youtube_url) }}" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
        </div>
        @endif

        {{-- Guest Info --}}
        @if($episode->guest_name)
        <div class="rounded-xl p-6 mb-8" style="background-color: {{ $podcast->color }}08">
            <h2 class="font-semibold mb-2">About the Guest</h2>
            <p class="text-gray-700 dark:text-gray-300">
                <strong>{{ $episode->guest_name }}</strong>
                @if($episode->guest_title) — {{ $episode->guest_title }} @endif
            </p>
            @if($episode->guest_url)
            <a href="{{ $episode->guest_url }}" target="_blank" class="text-sm hover:underline mt-2 inline-block" style="color: {{ $podcast->color }}">{{ $episode->guest_url }}</a>
            @endif
        </div>
        @endif

        {{-- Show Notes --}}
        @if($episode->show_notes)
        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Show Notes</h2>
            <div class="prose prose-lg dark:prose-invert max-w-none">
                {!! Str::markdown($episode->show_notes) !!}
            </div>
        </div>
        @endif

        {{-- Tags --}}
        @if($episode->tags->count())
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex flex-wrap gap-2">
                @foreach($episode->tags as $tag)
                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-sm rounded-full">{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Prev / Next --}}
        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800 grid grid-cols-2 gap-6">
            @if($prevEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $prevEpisode]) }}" class="group">
                <span class="text-xs text-gray-500">← Previous</span>
                <p class="font-semibold group-hover:opacity-80 transition-opacity">{{ $prevEpisode->title }}</p>
            </a>
            @else <div></div> @endif
            @if($nextEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $nextEpisode]) }}" class="group text-right">
                <span class="text-xs text-gray-500">Next →</span>
                <p class="font-semibold group-hover:opacity-80 transition-opacity">{{ $nextEpisode->title }}</p>
            </a>
            @endif
        </div>
    </div>
@endsection
