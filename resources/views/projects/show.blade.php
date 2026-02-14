@extends('layouts.app')

@section('title', $project->title)

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
    .link-btn {
        transition: all 0.2s ease;
    }
    .link-btn:hover {
        transform: translateY(-2px);
    }
</style>

    {{-- Project Header --}}
    <div class="noise-overlay border-b border-[#1e2a3a]">
        <div class="absolute inset-0 bg-gradient-to-br from-[#4A7FBF]/5 via-transparent to-transparent"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
                <a href="{{ route('projects.index') }}" class="hover:text-gray-300 transition-colors">Projects</a>
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-400">{{ $project->title }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 items-start">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-4">
                        @if($project->is_featured)
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-[#4A7FBF] bg-[#4A7FBF]/10 rounded-full border border-[#4A7FBF]/20">⭐ Featured</span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4 leading-tight">{{ $project->title }}</h1>
                    <p class="text-lg text-gray-400 leading-relaxed mb-8 max-w-3xl">{{ $project->description }}</p>

                    {{-- Action buttons --}}
                    <div class="flex flex-wrap gap-3">
                        @if($project->url)
                        <a href="{{ $project->url }}" target="_blank" class="link-btn inline-flex items-center gap-2 px-5 py-2.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            View Live
                        </a>
                        @endif
                        @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" class="link-btn inline-flex items-center gap-2 px-5 py-2.5 border border-[#1e2a3a] hover:border-gray-600 text-sm font-semibold rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            View Source
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Tech Stack Sidebar Card --}}
                @if($project->tech_stack)
                <div class="w-full lg:w-72 flex-shrink-0 p-5 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Tech Stack</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stack as $tech)
                        <span class="px-3 py-1.5 text-xs font-medium rounded-lg border border-[#1e2a3a] text-gray-400 bg-[#1e2a3a]/30">{{ $tech }}</span>
                        @endforeach
                    </div>

                    @if($project->tags->count())
                    <div class="mt-5 pt-5 border-t border-[#1e2a3a]">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Topics</h3>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($project->tags as $tag)
                            <span class="px-2.5 py-1 text-[11px] font-medium rounded-full text-[#4A7FBF] bg-[#4A7FBF]/10">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Featured Image --}}
    @if($project->featured_image)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mb-8 relative z-10">
        <div class="rounded-2xl overflow-hidden border border-[#1e2a3a] shadow-2xl mt-[-2rem]">
            <img src="{{ Storage::url($project->featured_image) }}" alt="{{ $project->title }}" class="w-full">
        </div>
    </div>
    @endif

    {{-- Content --}}
    <div class="dot-grid-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            @if($project->content)
            <div class="prose prose-invert prose-lg max-w-none
                prose-headings:text-gray-100 prose-headings:font-extrabold
                prose-h2:text-2xl prose-h2:mt-12 prose-h2:mb-4 prose-h2:pb-3 prose-h2:border-b prose-h2:border-[#1e2a3a]
                prose-a:text-[#4A7FBF] prose-a:no-underline hover:prose-a:underline
                prose-code:text-[#E47A9D] prose-code:font-mono
                prose-pre:bg-[#0D1117] prose-pre:border prose-pre:border-[#1e2a3a]
                prose-li:text-gray-400 prose-p:text-gray-400
                prose-strong:text-gray-200">
                {!! $project->content !!}
            </div>
            @else
            <div class="text-center py-16">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#4A7FBF]/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-gray-400 text-lg font-medium mb-2">Detailed write-up coming soon</p>
                <p class="text-gray-500 text-sm">Check back later for a full breakdown of the architecture and decisions behind this project.</p>
            </div>
            @endif

            {{-- Back to projects --}}
            <div class="mt-16 pt-8 border-t border-[#1e2a3a]">
                <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to all projects
                </a>
            </div>
        </div>
    </div>
@endsection
