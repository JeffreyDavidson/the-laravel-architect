@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <header class="mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">{{ $project->title }}</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">{{ $project->description }}</p>

            <div class="flex flex-wrap gap-3 mb-6">
                @if($project->url)
                <a href="{{ $project->url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    🔗 View Live
                </a>
                @endif
                @if($project->github_url)
                <a href="{{ $project->github_url }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 hover:border-gray-400 text-sm font-medium rounded-lg transition-colors">
                    📦 View Source
                </a>
                @endif
            </div>

            @if($project->tech_stack)
            <div class="flex flex-wrap gap-2">
                @foreach($project->tech_stack as $tech)
                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-sm font-medium rounded-full">{{ $tech }}</span>
                @endforeach
            </div>
            @endif
        </header>

        @if($project->featured_image)
        <div class="rounded-xl overflow-hidden mb-10 bg-gray-100 dark:bg-gray-800">
            <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full">
        </div>
        @endif

        @if($project->content)
        <div class="prose prose-lg dark:prose-invert max-w-none prose-headings:font-bold prose-a:text-indigo-600 dark:prose-a:text-indigo-400">
            {!! Str::markdown($project->content) !!}
        </div>
        @endif
    </div>
@endsection
