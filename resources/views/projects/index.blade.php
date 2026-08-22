@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<header class="noise-overlay relative overflow-hidden border-b border-gray-200 bg-white dark:border-brand-700 dark:bg-transparent">
    <div class="absolute left-1/4 top-1/4 h-[600px] w-[600px] rounded-full bg-brand-600 opacity-0 blur-[120px] dark:opacity-[0.06]"></div>
    <div class="absolute bottom-0 right-1/3 h-[400px] w-[400px] rounded-full bg-accent-400 opacity-0 blur-[100px] dark:opacity-[0.04]"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
        <x-terminal-prompt command="project:list" />

        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Selected work</p>
        <h1 class="mb-4 max-w-4xl text-4xl font-extrabold text-gray-900 dark:text-white md:text-5xl">Laravel case studies, not just screenshots</h1>
        <p class="max-w-3xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">A closer look at the domain problems, architecture decisions, and tradeoffs behind products I have built and maintained.</p>

        <dl class="mt-8 grid max-w-2xl grid-cols-3 gap-3">
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 dark:border-brand-700 dark:bg-brand-950/50">
                <dd class="font-mono text-2xl font-bold text-brand-600">{{ $projects->count() }}</dd>
                <dt class="mt-1 text-[11px] uppercase tracking-wider text-gray-500">Case studies</dt>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 dark:border-brand-700 dark:bg-brand-950/50">
                <dd class="font-mono text-2xl font-bold text-brand-600">{{ $projects->where('is_featured', true)->count() }}</dd>
                <dt class="mt-1 text-[11px] uppercase tracking-wider text-gray-500">Featured</dt>
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 dark:border-brand-700 dark:bg-brand-950/50">
                <dd class="font-mono text-2xl font-bold text-brand-600">{{ $projects->pluck('tech_stack')->flatten()->unique()->count() }}</dd>
                <dt class="mt-1 text-[11px] uppercase tracking-wider text-gray-500">Technologies</dt>
            </div>
        </dl>
    </div>
</header>

<main class="dot-grid-bg bg-gray-50 dark:bg-transparent">
    <div class="mx-auto max-w-7xl space-y-16 px-4 py-12 sm:px-6 md:py-20 lg:px-8">
        @if($projects->where('is_featured', true)->isNotEmpty())
            <section aria-labelledby="featured-projects-heading">
                <div class="mb-7 max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Deep dives</p>
                    <h2 id="featured-projects-heading" class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl">Featured case studies</h2>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    @foreach($projects->where('is_featured', true) as $project)
                        <article>
                            <a href="{{ route('projects.show', $project) }}" class="featured-card group relative block h-full overflow-hidden rounded-2xl border border-gray-200 bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-4 dark:border-brand-700 dark:bg-brand-950 dark:ring-offset-brand-950">
                                <div class="card-glow absolute inset-0 rounded-2xl shadow-[inset_0_0_80px_rgba(74,127,191,0.06),0_0_40px_rgba(74,127,191,0.04)]"></div>
                                <div class="h-[2px] w-full bg-gradient-to-r from-transparent via-brand-600 to-transparent"></div>

                                <div class="relative flex h-full flex-col p-6 sm:p-8">
                                    <div class="mb-6 flex items-start justify-between">
                                        <div class="card-icon flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600/20 to-brand-600/5">
                                            <x-svg-icon name="folder" class="h-5 w-5 text-brand-600" />
                                        </div>
                                        <span class="rounded-full border border-brand-600/20 bg-brand-600/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-brand-600">Case study</span>
                                    </div>

                                    <h3 class="mb-3 text-2xl font-extrabold transition-colors group-hover:text-brand-600">{{ $project->title }}</h3>
                                    <p class="mb-6 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $project->description }}</p>

                                    @if($project->tech_stack)
                                        <ul aria-label="Technology stack" class="mb-6 flex flex-wrap gap-1.5">
                                            @foreach($project->tech_stack as $tech)
                                                <li class="tech-pill rounded-md border border-gray-200 px-2.5 py-1 text-[11px] font-medium text-gray-500 dark:border-brand-700">{{ $tech }}</li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-brand-600">
                                        Read the case study
                                        <x-svg-icon name="arrow-long-right" class="card-arrow h-4 w-4" />
                                    </span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($projects->where('is_featured', false)->isNotEmpty())
            <section aria-labelledby="more-projects-heading">
                <div class="mb-7 max-w-2xl">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">More work</p>
                    <h2 id="more-projects-heading" class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl">Additional build notes</h2>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($projects->where('is_featured', false) as $project)
                        <x-projects.related-card :project="$project" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
@endsection
