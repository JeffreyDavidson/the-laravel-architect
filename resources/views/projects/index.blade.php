@extends('layouts.app')

@section('title', 'Projects')

@push('head')
    @vite('resources/css/pages/listings-entry.css')
@endpush

@section('content')
<x-hero-section>
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-end lg:gap-16">
        <div>
            <p class="mb-5 font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Selected work / 02</p>
            <h1 class="max-w-4xl text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">Laravel case studies, not just screenshots.</h1>
            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">A closer look at the domain problems, architecture decisions, and tradeoffs behind products I have built and maintained.</p>
        </div>

        <dl class="grid grid-cols-3 border-y border-gray-200 dark:border-[#1e2a3a]">
            <div class="py-4"><dd class="font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $projects->count() }}</dd><dt class="mt-1 text-xs uppercase tracking-wider text-gray-500">Studies</dt></div>
            <div class="border-x border-gray-200 px-4 py-4 dark:border-[#1e2a3a]"><dd class="font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $projects->where('is_featured', true)->count() }}</dd><dt class="mt-1 text-xs uppercase tracking-wider text-gray-500">Featured</dt></div>
            <div class="py-4 pl-4"><dd class="font-mono text-2xl font-semibold text-gray-900 dark:text-white">{{ $projects->pluck('tech_stack')->flatten()->unique()->count() }}</dd><dt class="mt-1 text-xs uppercase tracking-wider text-gray-500">Tools</dt></div>
        </dl>
    </div>
</x-hero-section>

<div class="bg-gray-50 dark:bg-[#0b1016]">
    <div class="mx-auto max-w-7xl space-y-20 px-4 py-12 sm:px-6 md:py-20 lg:px-8">
        @if($projects->where('is_featured', true)->isNotEmpty())
            <section aria-labelledby="featured-projects-heading">
                <div class="mb-8 grid gap-3 border-b border-gray-200 pb-5 md:grid-cols-[10rem_1fr] dark:border-[#1e2a3a]">
                    <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Featured</p>
                    <h2 id="featured-projects-heading" class="text-2xl font-semibold text-gray-900 dark:text-white">The work worth unpacking</h2>
                </div>
                <div class="divide-y divide-gray-200 border-b border-gray-200 dark:divide-[#1e2a3a] dark:border-[#1e2a3a]">
                    @foreach($projects->where('is_featured', true) as $project)
                        <article>
                            <a href="{{ route('projects.show', $project) }}" class="group grid gap-6 py-9 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-400 md:grid-cols-[8rem_minmax(0,1fr)_auto] md:items-start md:gap-10">
                                <span class="font-mono text-sm text-gray-500">0{{ $loop->iteration }}</span>
                                <div>
                                    <h3 class="text-2xl font-semibold text-gray-900 transition-colors group-hover:text-brand-600 dark:text-white md:text-3xl">{{ $project->title }}</h3>
                                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $project->description }}</p>
                                    @if($project->tech_stack)
                                        <ul aria-label="Technology stack" class="mt-5 flex flex-wrap gap-x-4 gap-y-2 font-mono text-xs uppercase tracking-wide text-gray-500">
                                            @foreach($project->tech_stack as $tech)<li>{{ $tech }}</li>@endforeach
                                        </ul>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600">Read the case study <x-svg-icon name="arrow-long-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($projects->where('is_featured', false)->isNotEmpty())
            <section aria-labelledby="more-projects-heading">
                <div class="mb-8 grid gap-3 border-b border-gray-200 pb-5 md:grid-cols-[10rem_1fr] dark:border-[#1e2a3a]">
                    <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Archive</p>
                    <h2 id="more-projects-heading" class="text-2xl font-semibold text-gray-900 dark:text-white">Additional build notes</h2>
                </div>
                <div class="grid grid-cols-1 gap-x-10 md:grid-cols-2">
                    @foreach($projects->where('is_featured', false) as $project)<x-projects.related-card :project="$project" />@endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
