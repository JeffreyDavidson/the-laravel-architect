@extends('layouts.app')

@section('content')
<style>
    @keyframes glowPulse {
        0%, 100% { opacity: 0.06; }
        50% { opacity: 0.1; }
    }

    .link-btn { transition: all 0.2s ease; }
    .link-btn:hover { transform: translateY(-2px); }

    .tech-tag { transition: all 0.15s ease; }
    .tech-tag:hover {
        background: rgba(74, 127, 191, 0.12);
        color: #4A7FBF;
        border-color: rgba(74, 127, 191, 0.3);
    }

    .related-card { transition: all 0.3s ease; }
    .related-card:hover {
        transform: translateY(-4px);
        border-color: rgba(74, 127, 191, 0.2);
    }
</style>

{{-- ===== PROJECT HERO ===== --}}
<section class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a]">
    {{-- Ambient glow --}}
    <div class="hidden dark:block absolute top-1/3 left-1/4 w-[600px] h-[600px] rounded-full blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%); animation: glowPulse 5s ease-in-out infinite;"></div>
    <div class="hidden dark:block absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #E47A9D, transparent 70%);"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="{{ route('projects.index') }}" class="hover:text-gray-300 dark:text-gray-700 dark:hover:text-gray-300 transition-colors">Projects</a>
            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600 dark:text-gray-400">{{ $project->title }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-10 lg:gap-16 items-start">
            <div class="flex-1 min-w-0">
                {{-- Badges --}}
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    @if($project->is_featured)
                    <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full border border-[#4A7FBF]/20">⭐ Featured</span>
                    @endif
                    @if($project->status === 'published')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-green-400 bg-green-500/10 rounded-full border border-green-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        Active
                    </span>
                    @endif
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-5 leading-tight">{{ $project->title }}</h1>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 leading-relaxed mb-8 max-w-3xl">{{ $project->description }}</p>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if($project->url)
                    <a href="{{ $project->url }}" target="_blank" class="link-btn inline-flex items-center gap-2 px-6 py-3 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white text-sm font-bold rounded-xl transition-colors" style="box-shadow: 0 0 20px rgba(74,127,191,0.3);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        View Live
                    </a>
                    @endif
                    @if($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" class="link-btn inline-flex items-center gap-2 px-6 py-3 border border-gray-200 dark:border-[#1e2a3a] hover:border-gray-500 text-sm font-bold rounded-xl transition-colors bg-white dark:bg-[#0D1117]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        View Source
                    </a>
                    @endif
                </div>
            </div>

            {{-- Tech Stack + Details Sidebar --}}
            <div class="w-full lg:w-80 flex-shrink-0 space-y-6">
                @if($project->tech_stack)
                <div class="p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Tech Stack</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="tech-tag px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 bg-[#1e2a3a]/30">{{ $tech }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($project->tags->count())
                <div class="p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Topics</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tags as $tag)
                        <span class="px-3 py-1.5 text-[11px] font-medium rounded-full text-[#4A7FBF] bg-[#4A7FBF]/10 border border-[#4A7FBF]/20">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Links card --}}
                @if($project->url || $project->github_url)
                <div class="p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Links</h3>
                    <div class="space-y-3">
                        @if($project->url)
                        <a href="{{ $project->url }}" target="_blank" class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 hover:text-[#4A7FBF] transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <span class="truncate">{{ parse_url($project->url, PHP_URL_HOST) }}</span>
                            <svg class="w-3 h-3 flex-shrink-0 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @endif
                        @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 hover:text-[#4A7FBF] transition-colors">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            <span class="truncate">{{ str_replace('https://github.com/', '', $project->github_url) }}</span>
                            <svg class="w-3 h-3 flex-shrink-0 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ===== FEATURED IMAGE ===== --}}
@if($project->hasMedia('featured_image'))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mb-8 relative z-10">
    <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-[#1e2a3a] shadow-2xl mt-[-2rem]">
        <img src="{{ $project->getFirstMediaUrl('featured_image') }}" alt="{{ $project->title }}" class="w-full">
    </div>
</div>
@endif

{{-- ===== CONTENT ===== --}}
<section class="dot-grid-bg">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        @if($project->content)
        <div class="prose prose-invert prose-lg max-w-none
            prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-headings:font-extrabold
            prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4 prose-h2:pb-3 prose-h2:border-b prose-h2:border-gray-200 dark:prose-h2:border-[#1e2a3a]
            prose-a:text-[#4A7FBF] prose-a:no-underline hover:prose-a:underline
            prose-code:text-[#E47A9D] prose-code:font-mono
            prose-pre:bg-gray-50 dark:prose-pre:bg-[#0D1117] prose-pre:border prose-pre:border-gray-200 dark:prose-pre:border-[#1e2a3a]
            prose-li:text-gray-600 dark:prose-li:text-gray-400 prose-p:text-gray-600 dark:prose-p:text-gray-400
            prose-strong:text-gray-800 dark:prose-strong:text-gray-200">
            {!! $project->content !!}
        </div>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#4A7FBF]/10 flex items-center justify-center">
                <svg class="w-8 h-8 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-lg font-medium mb-2">Detailed write-up coming soon</p>
            <p class="text-gray-500 text-sm">Check back later for a full breakdown of the architecture and decisions behind this project.</p>
        </div>
        @endif
    </div>
</section>

{{-- ===== MORE PROJECTS ===== --}}
@if($otherProjects->count())
<section class="border-t border-gray-200 dark:border-[#1e2a3a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-8">More Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($otherProjects->take(3) as $other)
            <a href="{{ route('projects.show', $other) }}" class="related-card group block p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]/50">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-[#4A7FBF]/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    @if($other->is_featured)
                    <span class="text-[10px] font-bold text-[#4A7FBF]">⭐</span>
                    @endif
                </div>
                <h3 class="font-bold mb-2 group-hover:text-[#4A7FBF] transition-colors">{{ $other->title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed line-clamp-2">{{ $other->description }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="border-t border-gray-200 dark:border-[#1e2a3a]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 dark:text-gray-700 dark:hover:text-gray-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to all projects
        </a>
    </div>
</section>
@endif
@endsection
