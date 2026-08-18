@extends('layouts.app')

@section('content')
{{-- ===== PROJECT HERO ===== --}}
<section class="noise-overlay relative overflow-hidden border-b border-gray-200 bg-white dark:border-brand-700 dark:bg-transparent">
    {{-- Ambient glow --}}
    <div class="project-hero-glow absolute left-1/4 top-1/3 h-[600px] w-[600px] rounded-full bg-brand-600 opacity-0 blur-[120px] dark:opacity-[0.06]"></div>
    <div class="absolute bottom-0 right-1/3 h-[400px] w-[400px] rounded-full bg-accent-400 opacity-0 blur-[100px] dark:opacity-[0.04]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('projects.index') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Projects</a>
            <x-svg-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-600" />
            <span class="text-gray-600 dark:text-gray-400">{{ $project->title }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-10 lg:gap-16 items-start">
            <div class="flex-1 min-w-0">
                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    @if($project->is_featured)
                    <span class="rounded-full border border-brand-600/20 bg-brand-600/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-brand-600">Featured</span>
                    @endif
                    @if($project->status === 'published')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-green-600 dark:text-green-400 bg-green-500/10 rounded-full border border-green-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        Active
                    </span>
                    @endif
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-5 leading-tight text-gray-900 dark:text-white">{{ $project->title }}</h1>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 leading-relaxed mb-8 max-w-3xl">{{ $project->description }}</p>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if($project->url)
                    <x-button href="{{ $project->url }}" target="_blank" class="link-btn shadow-[0_0_20px_rgba(74,127,191,0.3)]">
                        <x-svg-icon name="external-link" class="w-4 h-4" />
                        View Live
                    </x-button>
                    @endif
                    @if($project->github_url)
                    <x-button variant="outline" href="{{ $project->github_url }}" target="_blank" class="link-btn hover:border-gray-500">
                        <x-svg-icon name="github" class="w-4 h-4" />
                        View Source
                    </x-button>
                    @endif
                </div>
            </div>

            {{-- Tech Stack + Details Sidebar --}}
            <div class="w-full lg:w-80 flex-shrink-0 space-y-6">
                @if($project->tech_stack)
                <x-projects.sidebar-card title="Tech Stack">
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <x-projects.tech-pill :label="$tech" />
                        @endforeach
                    </div>
                </x-projects.sidebar-card>
                @endif

                @if($project->tags->count())
                <x-projects.sidebar-card title="Topics">
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tags as $tag)
                        <x-projects.topic-pill :label="$tag->name" />
                        @endforeach
                    </div>
                </x-projects.sidebar-card>
                @endif

                {{-- Links card --}}
                @if($project->url || $project->github_url)
                <x-projects.sidebar-card title="Links">
                    <div class="space-y-3">
                        @if($project->url)
                        <x-projects.detail-link :href="$project->url" icon="globe" :label="parse_url($project->url, PHP_URL_HOST)" />
                        @endif
                        @if($project->github_url)
                        <x-projects.detail-link :href="$project->github_url" icon="github" :label="str_replace('https://github.com/', '', $project->github_url)" />
                        @endif
                    </div>
                </x-projects.sidebar-card>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ===== FEATURED IMAGE ===== --}}
@if($project->featured_image_url)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mb-8 relative z-10">
    <div class="mt-[-2rem] overflow-hidden rounded-2xl border border-gray-200 shadow-2xl dark:border-brand-700">
        <img src="{{ $project->featured_image_url }}" alt="{{ $project->title }}" class="w-full">
    </div>
</div>
@endif

{{-- ===== CONTENT ===== --}}
<section class="dot-grid-bg bg-gray-50 dark:bg-transparent">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        @if($project->content)
        <x-prose class="prose-headings:font-extrabold prose-h2:mt-12 prose-h2:mb-4 prose-h2:border-b prose-h2:border-gray-200 prose-h2:pb-3 prose-h2:text-2xl prose-a:no-underline prose-code:font-mono prose-code:text-accent-400 prose-pre:border prose-pre:border-gray-200 prose-pre:bg-gray-50 prose-li:text-gray-600 prose-p:text-gray-600 prose-strong:text-gray-800 hover:prose-a:underline dark:prose-h2:border-brand-700 dark:prose-pre:border-brand-700 dark:prose-pre:bg-brand-950 dark:prose-li:text-gray-400 dark:prose-p:text-gray-400 dark:prose-strong:text-gray-200">
            {!! Str::markdown($project->content) !!}
        </x-prose>
        @else
        <div class="text-center py-16">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-600/10">
                <x-svg-icon name="document" class="h-8 w-8 text-brand-600" />
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium mb-2">Detailed write-up coming soon</p>
            <p class="text-gray-500 text-sm">Check back later for a full breakdown of the architecture and decisions behind this project.</p>
        </div>
        @endif
    </div>
</section>

{{-- ===== MORE PROJECTS ===== --}}
@if($otherProjects->count())
<section class="border-t border-gray-200 dark:border-brand-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-8">More Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($otherProjects->take(3) as $other)
            <x-projects.related-card :project="$other" />
            @endforeach
        </div>
    </div>
</section>
@else
<section class="border-t border-gray-200 dark:border-brand-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
            <x-svg-icon name="chevron-left" class="w-4 h-4" />
            Back to all projects
        </a>
    </div>
</section>
@endif
@endsection
