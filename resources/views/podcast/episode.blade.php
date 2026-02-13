@extends('layouts.app')

@section('title', $episode->title . ' — ' . $podcast->name)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('podcast.index') }}" class="hover:text-brand-400 transition-colors">Podcasts</a>
            <span>/</span>
            <a href="{{ route('podcast.show', $podcast) }}" class="hover:text-brand-400 transition-colors">{{ $podcast->name }}</a>
            <span>/</span>
            <span class="text-gray-400">{{ $episode->episode_code }}</span>
        </div>

        {{-- Header --}}
        <header class="mb-10">
            <div class="flex items-center gap-3 mb-4">
                <span class="font-mono text-sm font-semibold text-brand-400 px-2 py-1 bg-brand-800/50 rounded">{{ $episode->episode_code }}</span>
                <span class="text-sm text-gray-500">{{ $episode->published_at->format('F d, Y') }}</span>
                @if($episode->formatted_duration)
                <span class="text-sm text-gray-500">· {{ $episode->formatted_duration }}</span>
                @endif
            </div>
            <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $episode->title }}</h1>
            @if($episode->excerpt)
            <p class="text-lg text-gray-400">{{ $episode->excerpt }}</p>
            @endif
        </header>

        {{-- Audio Player --}}
        @if($episode->audio_url)
        <div class="mb-10 p-5 rounded-xl bg-brand-900/50 border border-brand-800/50">
            <p class="text-sm font-semibold text-gray-300 mb-3">Listen to this episode</p>
            <audio controls preload="none" class="w-full">
                <source src="{{ $episode->audio_url }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        </div>
        @endif

        {{-- Listen/Watch Links --}}
        @if($episode->spotify_url || $episode->apple_podcasts_url || $episode->video_url)
        <div class="flex flex-wrap gap-3 mb-10">
            @if($episode->spotify_url)
            <a href="{{ $episode->spotify_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#1DB954]/10 text-[#1DB954] text-sm font-medium rounded-lg hover:bg-[#1DB954]/20 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                Spotify
            </a>
            @endif
            @if($episode->apple_podcasts_url)
            <a href="{{ $episode->apple_podcasts_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#D56DFB]/10 text-[#D56DFB] text-sm font-medium rounded-lg hover:bg-[#D56DFB]/20 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5.34 0A5.328 5.328 0 000 5.34v13.32A5.328 5.328 0 005.34 24h13.32A5.328 5.328 0 0024 18.66V5.34A5.328 5.328 0 0018.66 0H5.34z"/></svg>
                Apple Podcasts
            </a>
            @endif
            @if($episode->video_url)
            <a href="{{ $episode->video_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-red-500/10 text-red-400 text-sm font-medium rounded-lg hover:bg-red-500/20 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                Watch on YouTube
            </a>
            @endif
        </div>
        @endif

        {{-- Guest Info --}}
        @if($episode->guest_name)
        <div class="mb-10 p-5 rounded-xl bg-brand-900/50 border border-brand-800/50">
            <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wide mb-2">Guest</h3>
            <p class="text-lg font-semibold text-white">{{ $episode->guest_name }}</p>
            @if($episode->guest_bio)
            <p class="text-gray-400 text-sm mt-1">{{ $episode->guest_bio }}</p>
            @endif
            @if($episode->guest_url)
            <a href="{{ $episode->guest_url }}" target="_blank" class="inline-block mt-2 text-sm text-brand-400 hover:underline">{{ $episode->guest_url }}</a>
            @endif
        </div>
        @endif

        {{-- Show Notes --}}
        @if($episode->content)
        <div class="prose prose-invert prose-lg max-w-none mb-12
            prose-headings:text-gray-100 prose-a:text-brand-400 prose-a:no-underline hover:prose-a:underline
            prose-code:font-mono prose-code:text-accent-400 prose-pre:bg-brand-900 prose-pre:border prose-pre:border-brand-800/50">
            <h2>Show Notes</h2>
            {!! $episode->content !!}
        </div>
        @endif

        {{-- Tags --}}
        @if($episode->tags->count())
        <div class="flex flex-wrap gap-2 mb-12">
            @foreach($episode->tags as $tag)
            <span class="px-3 py-1 bg-brand-800/50 text-gray-400 text-xs rounded-full">{{ $tag->name }}</span>
            @endforeach
        </div>
        @endif

        {{-- Prev / Next --}}
        <nav class="flex items-center justify-between pt-8 border-t border-brand-800/50">
            @if($prevEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $prevEpisode]) }}" class="group flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 text-gray-600 group-hover:text-brand-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <div>
                    <span class="text-xs text-gray-500">Previous</span>
                    <p class="font-medium group-hover:text-brand-400 transition-colors">{{ Str::limit($prevEpisode->title, 40) }}</p>
                </div>
            </a>
            @else
            <div></div>
            @endif

            @if($nextEpisode)
            <a href="{{ route('podcast.episode', [$podcast, $nextEpisode]) }}" class="group flex items-center gap-3 text-sm text-right">
                <div>
                    <span class="text-xs text-gray-500">Next</span>
                    <p class="font-medium group-hover:text-brand-400 transition-colors">{{ Str::limit($nextEpisode->title, 40) }}</p>
                </div>
                <svg class="w-5 h-5 text-gray-600 group-hover:text-brand-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <div></div>
            @endif
        </nav>
    </div>
@endsection
