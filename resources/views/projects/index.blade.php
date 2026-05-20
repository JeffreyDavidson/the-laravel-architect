@extends('layouts.app')

@section('title', 'Projects')

@section('content')
{{-- ===== HERO ===== --}}
<div class="noise-overlay relative overflow-hidden border-b border-gray-200 bg-white dark:border-brand-700 dark:bg-transparent">
    {{-- Ambient glow --}}
    <div class="absolute left-1/4 top-1/4 h-[600px] w-[600px] rounded-full bg-brand-600 opacity-0 blur-[120px] dark:opacity-[0.06]"></div>
    <div class="absolute bottom-0 right-1/3 h-[400px] w-[400px] rounded-full bg-accent-400 opacity-0 blur-[100px] dark:opacity-[0.04]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <x-terminal-prompt command="project:list" />

        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">Things I've Built</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">From passion projects to production platforms. Each one a lesson in architecture, testing, and building things that last.</p>

        {{-- Stats --}}
        <div class="flex gap-3 mt-6 max-w-md">
            <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-2 py-2.5 text-center dark:border-brand-700 dark:bg-brand-950/50 sm:px-3">
                <span class="block font-mono text-xl font-bold text-brand-600">{{ $projects->count() }}</span>
                <span class="text-[11px] text-gray-500 uppercase tracking-wider">Projects</span>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-2 py-2.5 text-center dark:border-brand-700 dark:bg-brand-950/50 sm:px-3">
                <span class="block font-mono text-xl font-bold text-brand-600">{{ $projects->where('is_featured', true)->count() }}</span>
                <span class="text-[11px] text-gray-500 uppercase tracking-wider">Featured</span>
            </div>
            <div class="flex-1 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 px-2 py-2.5 text-center dark:border-brand-700 dark:bg-brand-950/50 sm:px-3">
                <span class="block font-mono text-xl font-bold text-brand-600">{{ $projects->pluck('tech_stack')->flatten()->unique()->count() }}</span>
                <span class="text-[11px] text-gray-500 uppercase tracking-wider"><span class="sm:hidden">Tech</span><span class="hidden sm:inline">Technologies</span></span>
            </div>
        </div>
    </div>
</div>

{{-- ===== PROJECTS ===== --}}
<section class="dot-grid-bg bg-gray-50 dark:bg-transparent">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <div x-data="{ filter: 'all' }">
            {{-- Filter Tabs --}}
            <x-projects.filter-tabs />

            <div class="mt-10 space-y-16">

                {{-- Featured Projects --}}
                <x-projects.grid title="Featured Projects" show="filter === 'all' || filter === 'featured'">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($projects->where('is_featured', true) as $project)
                        <a href="{{ route('projects.show', $project) }}" class="featured-card group relative block overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-brand-700 dark:bg-brand-950">
                            {{-- Glow --}}
                            <div class="card-glow absolute inset-0 rounded-2xl shadow-[inset_0_0_80px_rgba(74,127,191,0.06),0_0_40px_rgba(74,127,191,0.04)]"></div>

                            {{-- Top accent --}}
                            <div class="h-[2px] w-full bg-gradient-to-r from-transparent via-brand-600 to-transparent"></div>

                            <div class="relative p-8">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-5">
                                    <div class="flex items-center gap-3">
                                        <div class="card-icon flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600/20 to-brand-600/5">
                                            <x-svg-icon name="folder" class="h-5 w-5 text-brand-600" />
                                        </div>
                                        <span class="rounded-full border border-brand-600/20 bg-brand-600/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-brand-600">Featured</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($project->github_url)
                                        <span class="text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors"><x-svg-icon name="github" class="w-5 h-5" /></span>
                                        @endif
                                        @if($project->url)
                                        <span class="text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors"><x-svg-icon name="external-link" class="w-5 h-5" /></span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Content --}}
                                <h3 class="mb-3 text-2xl font-extrabold transition-colors group-hover:text-brand-600">{{ $project->title }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6 line-clamp-3">{{ $project->description }}</p>

                                {{-- Tech Stack --}}
                                @if($project->tech_stack)
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach($project->tech_stack as $tech)
                                    <span class="tech-pill rounded-md border border-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-500 dark:border-brand-700">{{ $tech }}</span>
                                    @endforeach
                                </div>
                                @endif

                                {{-- CTA --}}
                                <div class="mt-2 flex items-center gap-2 text-sm font-semibold text-brand-600">
                                    <span>View Project</span>
                                    <x-svg-icon name="arrow-long-right" class="w-4 h-4 card-arrow" />
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </x-projects.grid>

                {{-- Other Projects --}}
                <x-projects.grid show="true">
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-6"
                        x-show="filter === 'all' || filter === 'opensource' || filter === 'client'"
                        x-text="filter === 'all' ? 'More Projects' : filter === 'opensource' ? 'Open Source' : 'Side Projects'">
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($projects as $project)
                        <a href="{{ route('projects.show', $project) }}"
                           class="project-card group relative block overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-brand-700 dark:bg-brand-950/50"
                           x-show="{{ $project->is_featured ? 'false' : "filter === 'all'" }}{{ $project->github_url ? " || filter === 'opensource'" : '' }}{{ !$project->github_url && !$project->is_featured ? " || filter === 'client'" : '' }}"
                           x-transition
                        >
                            {{-- Glow --}}
                            <div class="project-glow absolute inset-0 rounded-2xl shadow-[inset_0_0_60px_rgba(74,127,191,0.04)]"></div>

                            <div class="relative p-6">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600/15 to-brand-600/5">
                                        <x-svg-icon name="folder" class="h-4.5 w-4.5 text-brand-600" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($project->github_url)
                                        <x-svg-icon name="github" class="w-4 h-4 text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors" />
                                        @endif
                                        @if($project->url)
                                        <x-svg-icon name="external-link" class="w-4 h-4 text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors" />
                                        @endif
                                    </div>
                                </div>

                                {{-- Content --}}
                                <h3 class="mb-2 text-lg font-bold transition-colors group-hover:text-brand-600">{{ $project->title }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed mb-5 line-clamp-2">{{ $project->description }}</p>

                                {{-- Tech --}}
                                @if($project->tech_stack)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($project->tech_stack as $tech)
                                    <span class="tech-pill rounded border border-gray-200 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:border-brand-700">{{ $tech }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </x-projects.grid>

            </div>
        </div>
    </div>
</section>
@endsection
