@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<style>
    .noise-overlay {
        position: relative;
    }
    .noise-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.04;
        pointer-events: none;
        z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat;
        background-size: 256px 256px;
    }
    .dot-grid-bg {
        position: relative;
    }
    .dot-grid-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.03;
        pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px;
        z-index: 0;
    }
    .dot-grid-bg > * {
        position: relative;
        z-index: 1;
    }

    /* Project card hover */
    .project-card {
        transition: all 0.3s ease;
    }
    .project-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
    }
    .project-card:hover .project-glow {
        opacity: 1;
    }
    .project-glow {
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    /* Featured card */
    .featured-card {
        transition: all 0.3s ease;
    }
    .featured-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 50px -12px rgba(74, 127, 191, 0.15);
    }

    /* Filter tabs */
    .filter-tab {
        transition: all 0.2s ease;
    }
    .filter-tab.active {
        color: white;
        background: rgba(74, 127, 191, 0.15);
        border-color: #4A7FBF;
    }

    /* Tech pill hover */
    .tech-pill {
        transition: all 0.15s ease;
    }
    .tech-pill:hover {
        background: rgba(74, 127, 191, 0.15);
        color: #4A7FBF;
    }
</style>

    {{-- Hero --}}
    <div class="noise-overlay border-b border-[#1e2a3a]">
        <div class="absolute inset-0 bg-gradient-to-br from-[#4A7FBF]/5 via-transparent to-[#E47A9D]/5"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-6xl font-extrabold mb-4 tracking-tight">Things I've Built</h1>
                <p class="text-gray-400 text-lg md:text-xl leading-relaxed">From open-source packages to full-scale SaaS platforms — a collection of projects that reflect how I think about architecture, testing, and clean code.</p>
            </div>

            {{-- Stats --}}
            <div class="flex flex-wrap gap-8 mt-10">
                <div>
                    <span class="text-3xl font-extrabold text-[#4A7FBF]">{{ $projects->count() }}</span>
                    <span class="text-sm text-gray-500 ml-1.5">Projects</span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-[#4A7FBF]">{{ $projects->where('is_featured', true)->count() }}</span>
                    <span class="text-sm text-gray-500 ml-1.5">Featured</span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-[#4A7FBF]">{{ $projects->pluck('tech_stack')->flatten()->unique()->count() }}</span>
                    <span class="text-sm text-gray-500 ml-1.5">Technologies</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Projects --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">

        <div x-data="{ filter: 'all' }">
            {{-- Filter Tabs --}}
            <div class="flex flex-wrap gap-2 mb-12">
                <button @click="filter = 'all'" :class="filter === 'all' ? 'active' : ''" class="filter-tab px-4 py-2 text-sm font-medium rounded-lg border border-[#1e2a3a] text-gray-400 hover:text-white hover:border-gray-600">
                    All Projects
                </button>
                <button @click="filter = 'featured'" :class="filter === 'featured' ? 'active' : ''" class="filter-tab px-4 py-2 text-sm font-medium rounded-lg border border-[#1e2a3a] text-gray-400 hover:text-white hover:border-gray-600">
                    ⭐ Featured
                </button>
                <button @click="filter = 'opensource'" :class="filter === 'opensource' ? 'active' : ''" class="filter-tab px-4 py-2 text-sm font-medium rounded-lg border border-[#1e2a3a] text-gray-400 hover:text-white hover:border-gray-600">
                    Open Source
                </button>
                <button @click="filter = 'client'" :class="filter === 'client' ? 'active' : ''" class="filter-tab px-4 py-2 text-sm font-medium rounded-lg border border-[#1e2a3a] text-gray-400 hover:text-white hover:border-gray-600">
                    Client Work
                </button>

            {{-- Featured Projects (Large Cards) --}}
            <div class="mt-10 mb-12" x-show="filter === 'all' || filter === 'featured'">
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-6">Featured Projects</h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($projects->where('is_featured', true) as $project)
                    <a href="{{ route('projects.show', $project) }}" class="featured-card group relative block rounded-2xl border border-[#1e2a3a] bg-[#0D1117] overflow-hidden">
                        {{-- Top accent --}}
                        <div class="h-1 w-full bg-gradient-to-r from-[#4A7FBF] to-[#4A7FBF]/40"></div>

                        {{-- Glow --}}
                        <div class="project-glow absolute inset-0 rounded-2xl" style="box-shadow: inset 0 0 60px rgba(74,127,191,0.06), 0 0 30px rgba(74,127,191,0.04);"></div>

                        <div class="relative p-7">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full">Featured</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($project->github_url)
                                    <span class="text-gray-600 group-hover:text-gray-400 transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                    </span>
                                    @endif
                                    @if($project->url)
                                    <span class="text-gray-600 group-hover:text-gray-400 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Title + Description --}}
                            <h3 class="text-xl font-bold mb-2 group-hover:text-[#4A7FBF] transition-colors">{{ $project->title }}</h3>
                            <p class="text-gray-400 text-sm leading-relaxed mb-5 line-clamp-3">{{ $project->description }}</p>

                            {{-- Tech Stack --}}
                            @if($project->tech_stack)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($project->tech_stack as $tech)
                                <span class="tech-pill px-2.5 py-1 text-[11px] font-medium rounded-md border border-[#1e2a3a] text-gray-500">{{ $tech }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Arrow --}}
                        <div class="absolute bottom-7 right-7">
                            <svg class="w-5 h-5 text-gray-600 group-hover:text-[#4A7FBF] group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- All Other Projects (Compact Grid) --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-6" x-text="filter === 'all' ? 'More Projects' : filter === 'featured' ? '' : filter === 'opensource' ? 'Open Source' : 'Client Work'"></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                    <a href="{{ route('projects.show', $project) }}"
                       class="project-card group relative block p-5 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50 overflow-hidden"
                       x-show="{{ $project->is_featured ? 'false' : "filter === 'all'" }}{{ $project->github_url ? " || filter === 'opensource'" : '' }}{{ !$project->github_url && !$project->is_featured ? " || filter === 'client'" : '' }}"
                       x-transition
                    >
                        {{-- Glow --}}
                        <div class="project-glow absolute inset-0 rounded-xl" style="box-shadow: inset 0 0 40px rgba(74,127,191,0.04);"></div>

                        <div class="relative">
                            {{-- Header row --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-9 h-9 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($project->is_featured)
                                    <span class="text-[10px] font-bold text-[#4A7FBF]">⭐</span>
                                    @endif
                                    @if($project->github_url)
                                    <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                    @endif
                                    @if($project->url)
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    @endif
                                </div>
                            </div>

                            {{-- Title --}}
                            <h3 class="font-bold mb-1.5 group-hover:text-[#4A7FBF] transition-colors">{{ $project->title }}</h3>
                            <p class="text-gray-500 text-xs leading-relaxed mb-4 line-clamp-2">{{ $project->description }}</p>

                            {{-- Tech --}}
                            @if($project->tech_stack)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($project->tech_stack, 0, 4) as $tech)
                                <span class="text-[10px] font-medium text-gray-500">{{ $tech }}{{ !$loop->last ? ' ·' : '' }}</span>
                                @endforeach
                                @if(count($project->tech_stack) > 4)
                                <span class="text-[10px] text-gray-600">+{{ count($project->tech_stack) - 4 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            </div>
        </div>
        </div>
    </div>
@endsection
