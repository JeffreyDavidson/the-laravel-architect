@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<style>
    /* Filter tabs */
    .filter-tab {
        transition: all 0.2s ease;
    }
    .filter-tab.active {
        color: white;
        background: #4A7FBF;
        border-color: #4A7FBF;
    }
    :root:not(.dark) .filter-tab {
        color: #374151;
        border-color: #d1d5db;
    }
    :root:not(.dark) .filter-tab.active {
        color: white;
        background: #4A7FBF;
        border-color: #4A7FBF;
    }
    :root:not(.dark) .filter-tab:not(.active):hover {
        border-color: #4A7FBF;
        color: #4A7FBF;
    }

    /* Featured card */
    .featured-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .featured-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.5);
    }
    .featured-card:hover .card-glow {
        opacity: 1;
    }
    /* Light mode */
    :root:not(.dark) .featured-card:hover {
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.1);
    }
    .featured-card:hover .card-arrow {
        transform: translateX(4px);
    }
    .featured-card:hover .card-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    .card-glow { opacity: 0; transition: opacity 0.4s ease; }
    .card-arrow { transition: transform 0.3s ease; }
    .card-icon { transition: transform 0.3s ease; }

    /* Project card */
    .project-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .project-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.4);
        border-color: rgba(74, 127, 191, 0.2);
    }
    .project-card:hover .project-glow {
        opacity: 1;
    }
    .project-glow { opacity: 0; transition: opacity 0.3s ease; }

    /* Tech pill */
    .tech-pill {
        transition: all 0.15s ease;
    }
    .tech-pill:hover {
        background: rgba(74, 127, 191, 0.12);
        color: #4A7FBF;
        border-color: rgba(74, 127, 191, 0.3);
    }
    :root:not(.dark) .tech-pill {
        color: #4b5563;
        border-color: #d1d5db;
        background: #f9fafb;
    }
    :root:not(.dark) .project-card,
    :root:not(.dark) .featured-card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    :root:not(.dark) .project-card:hover {
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    /* Terminal cursor blink */
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
</style>

{{-- ===== HERO ===== --}}
<div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-transparent">
    {{-- Ambient glow --}}
    <div class="absolute top-1/4 left-1/4 w-[600px] h-[600px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
    <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-0 dark:opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #E47A9D, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <x-terminal-prompt command="project:list" />

        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 text-gray-900 dark:text-white">Things I've Built</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl">From passion projects to production platforms. Each one a lesson in architecture, testing, and building things that last.</p>

        {{-- Stats --}}
        <div class="flex gap-3 mt-6 max-w-md">
            <div class="flex-1 text-center px-2 sm:px-3 py-2.5 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                <span class="block text-xl font-mono font-bold text-[#4A7FBF]">{{ $projects->count() }}</span>
                <span class="text-[11px] text-gray-500 uppercase tracking-wider">Projects</span>
            </div>
            <div class="flex-1 text-center px-2 sm:px-3 py-2.5 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                <span class="block text-xl font-mono font-bold text-[#4A7FBF]">{{ $projects->where('is_featured', true)->count() }}</span>
                <span class="text-[11px] text-gray-500 uppercase tracking-wider">Featured</span>
            </div>
            <div class="flex-1 text-center px-2 sm:px-3 py-2.5 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50 overflow-hidden">
                <span class="block text-xl font-mono font-bold text-[#4A7FBF]">{{ $projects->pluck('tech_stack')->flatten()->unique()->count() }}</span>
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
                        <a href="{{ route('projects.show', $project) }}" class="featured-card group relative block rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117] overflow-hidden">
                            {{-- Glow --}}
                            <div class="card-glow absolute inset-0 rounded-2xl" style="box-shadow: inset 0 0 80px rgba(74,127,191,0.06), 0 0 40px rgba(74,127,191,0.04);"></div>

                            {{-- Top accent --}}
                            <div class="h-[2px] w-full" style="background: linear-gradient(90deg, transparent, #4A7FBF, transparent);"></div>

                            <div class="relative p-8">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-5">
                                    <div class="flex items-center gap-3">
                                        <div class="card-icon w-11 h-11 rounded-xl bg-gradient-to-br from-[#4A7FBF]/20 to-[#4A7FBF]/5 flex items-center justify-center">
                                            <x-svg-icon name="folder" class="w-5 h-5 text-[#4A7FBF]" />
                                        </div>
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full border border-[#4A7FBF]/20">Featured</span>
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
                                <h3 class="text-2xl font-extrabold mb-3 group-hover:text-[#4A7FBF] transition-colors">{{ $project->title }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6 line-clamp-3">{{ $project->description }}</p>

                                {{-- Tech Stack --}}
                                @if($project->tech_stack)
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach($project->tech_stack as $tech)
                                    <span class="tech-pill px-2.5 py-1 text-[11px] font-medium rounded-md border border-gray-200 dark:border-[#1e2a3a] text-gray-500">{{ $tech }}</span>
                                    @endforeach
                                </div>
                                @endif

                                {{-- CTA --}}
                                <div class="flex items-center gap-2 text-sm font-semibold text-[#4A7FBF] mt-2">
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
                           class="project-card group relative block rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]/50 overflow-hidden"
                           x-show="{{ $project->is_featured ? 'false' : "filter === 'all'" }}{{ $project->github_url ? " || filter === 'opensource'" : '' }}{{ !$project->github_url && !$project->is_featured ? " || filter === 'client'" : '' }}"
                           x-transition
                        >
                            {{-- Glow --}}
                            <div class="project-glow absolute inset-0 rounded-2xl" style="box-shadow: inset 0 0 60px rgba(74,127,191,0.04);"></div>

                            <div class="relative p-6">
                                {{-- Header --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#4A7FBF]/15 to-[#4A7FBF]/5 flex items-center justify-center flex-shrink-0">
                                        <x-svg-icon name="folder" class="w-4.5 h-4.5 text-[#4A7FBF]" />
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
                                <h3 class="text-lg font-bold mb-2 group-hover:text-[#4A7FBF] transition-colors">{{ $project->title }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed mb-5 line-clamp-2">{{ $project->description }}</p>

                                {{-- Tech --}}
                                @if($project->tech_stack)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($project->tech_stack as $tech)
                                    <span class="tech-pill px-2 py-0.5 text-[10px] font-medium rounded border border-gray-200 dark:border-[#1e2a3a] text-gray-600">{{ $tech }}</span>
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
