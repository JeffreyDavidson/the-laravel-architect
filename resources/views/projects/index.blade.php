@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div data-project-index>
        <header class="pt-12 pb-10 sm:pt-20 sm:pb-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                    Selected projects
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Explore the products I’ve built, the problems they solve, and the work behind them.
                </p>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 sm:pb-20 lg:px-8">
            @if ($technologies->isNotEmpty() || $tags->isNotEmpty())
                <form
                    method="GET"
                    action="{{ route('projects.index') }}"
                    class="dark:border-brand-800 mb-10 flex flex-col gap-4 border-y border-gray-200 py-6 sm:flex-row sm:items-end"
                >
                    <div class="w-full sm:max-w-xs">
                        <label
                            for="project-technology"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Technology</label>
                        <select
                            id="project-technology"
                            name="technology"
                            class="focus:border-brand-600 focus:ring-brand-600 dark:border-brand-700 dark:bg-brand-950 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 focus:ring-1 focus:outline-none dark:text-gray-100"
                        >
                            <option value="">All technologies</option>
                            @foreach ($technologies as $technology)
                                <option value="{{ $technology }}" @selected($selectedTechnology === $technology)>
                                    {{ $technology }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:max-w-xs">
                        <label for="project-tag" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Topic</label>
                        <select
                            id="project-tag"
                            name="tag"
                            class="focus:border-brand-600 focus:ring-brand-600 dark:border-brand-700 dark:bg-brand-950 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 focus:ring-1 focus:outline-none dark:text-gray-100"
                        >
                            <option value="">All topics</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->slug }}" @selected($selectedTag === $tag->slug)>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="focus-visible:outline-brand-500 bg-brand-600 hover:bg-brand-700 rounded-lg px-5 py-2.5 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4"
                    >
                        Filter projects
                    </button>
                    @if ($hasFilters)
                        <a
                            href="{{ route('projects.index') }}"
                            class="text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-brand-100 px-1 py-2.5 text-sm font-semibold"
                        >Clear filters</a>
                    @endif
                </form>
            @endif

            @if ($projects->isEmpty())
                @if ($hasFilters)
                    <p class="dark:border-brand-800 border-t border-gray-200 pt-8 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        No projects match those filters.
                        <a
                            href="{{ route('projects.index') }}"
                            class="text-brand-700 hover:text-brand-900 dark:text-brand-300 dark:hover:text-brand-100 font-semibold"
                        >Show all projects</a>
                    </p>
                @else
                    <p class="dark:border-brand-800 border-t border-gray-200 pt-8 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        Project details aren’t available here yet. Get in touch if you’d like to discuss my work.
                    </p>
                @endif
            @else
                @if ($projects->where('is_featured', true)->isNotEmpty())
                    <section aria-labelledby="featured-projects-heading">
                        <h2 id="featured-projects-heading" class="sr-only">Featured projects</h2>
                        <div class="dark:divide-brand-800 dark:border-brand-800 divide-y divide-gray-200 border-y border-gray-200">
                            @foreach ($projects->where('is_featured', true) as $project)
                                <x-projects.index-entry :project="$project" :priority="$loop->first" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($projects->where('is_featured', false)->isNotEmpty())
                    <section
                        aria-labelledby="more-projects-heading"
                        @class(['mt-12 sm:mt-16' => $projects->where('is_featured', true)->isNotEmpty()])
                    >
                        <h2
                            id="more-projects-heading"
                            class="mb-6 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white"
                        >
                            More projects
                        </h2>
                        <div class="dark:divide-brand-800 dark:border-brand-800 divide-y divide-gray-200 border-y border-gray-200">
                            @foreach ($projects->where('is_featured', false) as $project)
                                <x-projects.index-entry
                                    :project="$project"
                                    :priority="$loop->first && $projects->where('is_featured', true)->isEmpty()"
                                />
                            @endforeach
                        </div>
                    </section>
                @endif
            @endif
        </div>
    </div>

    <section
        aria-labelledby="projects-contact-heading"
        class="dark:border-brand-800 dark:bg-brand-900/30 border-t border-gray-200 bg-gray-50 py-10 sm:py-14"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <h2
                    id="projects-contact-heading"
                    class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white"
                >
                    What are you working on?
                </h2>
                <p class="mt-3 text-base leading-7 text-gray-600 dark:text-gray-400">
                    Tell me what you’re building, what needs to change, or where you’re stuck.
                </p>
            </div>
            <a
                href="{{ route('contact') }}"
                class="focus-visible:outline-brand-500 bg-brand-600 hover:bg-brand-700 w-fit shrink-0 rounded-lg px-5 py-3 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4"
            >Discuss a Project</a>
        </div>
    </section>
@endsection
