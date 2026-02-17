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
<section class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-transparent">
    {{-- Ambient glow --}}
    <div class="absolute top-1/3 left-1/4 w-[600px] h-[600px] rounded-full opacity-0 dark:opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%); animation: glowPulse 5s ease-in-out infinite;"></div>
    <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-0 dark:opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #E47A9D, transparent 70%);"></div>

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
                    <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full border border-[#4A7FBF]/20">⭐ Featured</span>
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
                    <x-button href="{{ $project->url }}" target="_blank" class="link-btn" style="box-shadow: 0 0 20px rgba(74,127,191,0.3);">
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
                <div class="p-6 rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Tech Stack</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="tech-tag px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-[#1e2a3a]/30">{{ $tech }}</span>
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
                            <x-svg-icon name="globe" class="w-4 h-4 flex-shrink-0" />
                            <span class="truncate">{{ parse_url($project->url, PHP_URL_HOST) }}</span>
                            <x-svg-icon name="external-link" class="w-3 h-3 flex-shrink-0 ml-auto" />
                        </a>
                        @endif
                        @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 hover:text-[#4A7FBF] transition-colors">
                            <x-svg-icon name="github" class="w-4 h-4 flex-shrink-0" />
                            <span class="truncate">{{ str_replace('https://github.com/', '', $project->github_url) }}</span>
                            <x-svg-icon name="external-link" class="w-3 h-3 flex-shrink-0 ml-auto" />
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
<section class="dot-grid-bg bg-gray-50 dark:bg-transparent">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        @if($project->content)
        <x-prose class="prose-headings:font-extrabold prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4 prose-h2:pb-3 prose-h2:border-b prose-h2:border-gray-200 dark:prose-h2:border-[#1e2a3a] prose-a:no-underline hover:prose-a:underline prose-code:text-[#E47A9D] prose-code:font-mono prose-pre:bg-gray-50 dark:prose-pre:bg-[#0D1117] prose-pre:border prose-pre:border-gray-200 dark:prose-pre:border-[#1e2a3a] prose-li:text-gray-600 dark:prose-li:text-gray-400 prose-p:text-gray-600 dark:prose-p:text-gray-400 prose-strong:text-gray-800 dark:prose-strong:text-gray-200">
            {!! $project->content !!}
        </x-prose>
        @else
        <div class="text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#4A7FBF]/10 flex items-center justify-center">
                <x-svg-icon name="document" class="w-8 h-8 text-[#4A7FBF]" />
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
                        <x-svg-icon name="folder" class="w-4 h-4 text-[#4A7FBF]" />
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
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-gray-300 transition-colors">
            <x-svg-icon name="chevron-left" class="w-4 h-4" />
            Back to all projects
        </a>
    </div>
</section>
@endif
@endsection
