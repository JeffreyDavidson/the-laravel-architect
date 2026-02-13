@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold mb-2">Projects</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-10">A collection of things I've built, contributed to, and am proud of.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="group block bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden hover:border-indigo-300 dark:hover:border-indigo-700 transition-all hover:shadow-lg">
                @if($project->featured_image)
                <div class="aspect-video bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                @endif
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-2">
                        <h2 class="font-semibold text-lg group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $project->title }}</h2>
                        @if($project->is_featured)
                        <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded">Featured</span>
                        @endif
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $project->description }}</p>
                    @if($project->tech_stack)
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($project->tech_stack as $tech)
                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-xs font-medium rounded">{{ $tech }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        @if($project->url)
                        <span class="flex items-center gap-1">🔗 Live</span>
                        @endif
                        @if($project->github_url)
                        <span class="flex items-center gap-1">📦 Source</span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-2 text-center py-20 text-gray-500">
                <p class="text-lg">Projects coming soon!</p>
            </div>
            @endforelse
        </div>
    </div>
@endsection
