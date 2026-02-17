@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<style>
    .noise-overlay { position: relative; }
    .dark .noise-overlay::after {
        content: ''; position: absolute; inset: 0; opacity: 0.04; pointer-events: none; z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat; background-size: 256px 256px;
    }
    .dot-grid-bg { position: relative; }
    .dark .dot-grid-bg::before {
        content: ''; position: absolute; inset: 0; opacity: 0.03; pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px; z-index: 0;
    }
    .dot-grid-bg > * { position: relative; z-index: 1; }

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
    <div class="hidden dark:block absolute top-1/4 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
    <div class="hidden dark:block absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #E47A9D, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="flex items-center gap-3 mb-4">
            <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
                <span class="text-[#4A7FBF]">$</span>
                <span>php artisan project:list</span>
                <span class="animate-pulse text-gray-400 dark:text-[#4A7FBF] relative -top-px">▊</span>
            </div>
        </div>

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
<section class="dot-grid-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
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
                                            <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                        </div>
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full border border-[#4A7FBF]/20">Featured</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($project->github_url)
                                        <span class="text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg></span>
                                        @endif
                                        @if($project->url)
                                        <span class="text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
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
                                    <svg class="w-4 h-4 card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
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
                                        <svg class="w-4.5 h-4.5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($project->github_url)
                                        <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                        @endif
                                        @if($project->url)
                                        <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-600 dark:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
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
