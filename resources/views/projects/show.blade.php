@extends('layouts.app')

@push('head')
    @vite('resources/css/pages/listings-entry.css')
@endpush

@section('content')
<header class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016]">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('projects.index') }}" class="hover:text-gray-900 dark:hover:text-gray-300 transition-colors">Projects</a>
            <x-svg-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-600" />
            <span class="text-gray-600 dark:text-gray-400">{{ $project->title }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row gap-10 lg:gap-16 items-start">
            <div class="flex-1 min-w-0">
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <span class="rounded-full border border-brand-600/20 bg-brand-600/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-brand-600">Project case study</span>
                    @if($project->is_featured)
                        <span class="rounded-full border border-accent-400/30 bg-accent-400/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-[#7f334d] dark:text-accent-400">Featured work</span>
                    @endif
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-5 leading-tight text-gray-900 dark:text-white">{{ $project->title }}</h1>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 leading-relaxed mb-8 max-w-3xl">{{ $project->description }}</p>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if($project->url)
                    <x-button href="{{ $project->url }}" target="_blank" rel="noopener noreferrer" class="link-btn">
                        <x-svg-icon name="external-link" class="w-4 h-4" />
                        Visit the project
                    </x-button>
                    @endif
                    @if($project->github_url)
                    <x-button variant="outline" href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="link-btn hover:border-gray-500">
                        <x-svg-icon name="github" class="w-4 h-4" />
                        Explore the code
                    </x-button>
                    @endif
                </div>
            </div>

            <aside aria-label="Project details" class="w-full flex-shrink-0 space-y-6 lg:w-80">
                <x-projects.sidebar-card title="Project brief">
                    <dl class="space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Format</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200">Case study</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Code</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $project->github_url ? 'Available' : 'Private' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500">Product</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200">{{ $project->url ? 'Live link' : 'Build notes' }}</dd>
                        </div>
                    </dl>
                </x-projects.sidebar-card>

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
            </aside>
        </div>
    </div>
</header>

{{-- ===== FEATURED IMAGE ===== --}}
@if($project->featured_image_url)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mb-8 relative z-10">
    <div class="mt-[-2rem] overflow-hidden rounded-2xl border border-gray-200 shadow-2xl dark:border-brand-700">
        <img src="{{ $project->featured_image_url }}" alt="{{ $project->title }}" decoding="async" fetchpriority="high" class="w-full">
    </div>
</div>
@endif

<section class="bg-gray-50 dark:bg-[#0b1016]">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:py-20 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-16 lg:px-8">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Behind the build</p>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Decisions over decoration</h2>
            <p class="mt-3 text-sm leading-relaxed text-gray-500">The product context, technical choices, constraints, and lessons that shaped this work.</p>
        </div>

        <div>
        @if($project->content)
            <x-prose class="prose-headings:font-extrabold prose-h2:mt-12 prose-h2:mb-4 prose-h2:border-b prose-h2:border-gray-200 prose-h2:pb-3 prose-h2:text-2xl prose-h2:first:mt-0 prose-a:no-underline prose-code:font-mono prose-code:text-accent-400 prose-pre:border prose-pre:border-gray-200 prose-pre:bg-gray-50 prose-li:text-gray-600 prose-p:text-gray-600 prose-strong:text-gray-800 hover:prose-a:underline dark:prose-h2:border-brand-700 dark:prose-pre:border-brand-700 dark:prose-pre:bg-brand-950 dark:prose-li:text-gray-400 dark:prose-p:text-gray-400 dark:prose-strong:text-gray-200">
                {!! Str::markdown($project->content) !!}
            </x-prose>
        @else
            <div class="rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center dark:border-brand-700 dark:bg-brand-950/50">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-600/10">
                    <x-svg-icon name="document" class="h-8 w-8 text-brand-600" />
                </div>
                <p class="mb-2 text-lg font-medium text-gray-600 dark:text-gray-400">Detailed write-up coming soon</p>
                <p class="text-sm text-gray-500">Check back later for a full breakdown of the architecture and decisions behind this project.</p>
            </div>
        @endif
        </div>
    </div>
</section>

{{-- ===== MORE PROJECTS ===== --}}
@if($otherProjects->count())
<section class="border-t border-gray-200 dark:border-brand-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
        <div class="mb-8 flex items-end justify-between gap-6">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Keep exploring</p>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">More project stories</h2>
            </div>
            <a href="{{ route('projects.index') }}" class="hidden items-center gap-2 text-sm font-semibold text-brand-600 hover:underline sm:inline-flex">
                All projects
                <x-svg-icon name="arrow-long-right" class="h-4 w-4" />
            </a>
        </div>
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
