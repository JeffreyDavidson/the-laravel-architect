@extends('layouts.app')

@section('content')
    <article data-project-detail>
        <section class="dark:border-brand-800 dark:bg-brand-950 border-b border-gray-200 bg-white py-10 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="mb-10">
                    <a
                        href="{{ route('projects.index') }}"
                        class="text-brand-700 focus-visible:outline-brand-500 dark:text-brand-300 inline-flex items-center gap-2 rounded py-2 text-sm font-medium hover:underline focus-visible:outline-2 focus-visible:outline-offset-4"
                    >
                        <x-heroicon-o-arrow-long-left class="size-5 shrink-0" aria-hidden="true" />
                        All projects
                    </a>
                </nav>
                <div class="grid gap-6 lg:grid-cols-2 lg:gap-16">
                    <h1 class="min-w-0 text-4xl font-semibold tracking-tight text-balance break-words text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                        {{ $project->title }}
                    </h1>
                    <div>
                        <p class="max-w-xl text-lg leading-8 text-gray-600 sm:text-xl dark:text-gray-300">
                            {{ $project->description }}
                        </p>
                        @if ($project->url)
                            <a
                                href="{{ $project->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-brand-700 hover:text-brand-900 focus-visible:outline-brand-500 dark:text-brand-300 mt-6 inline-flex items-center gap-2 rounded py-2 text-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 dark:hover:text-white"
                            >
                                Visit the project<span class="sr-only"> (opens in a new tab)</span>
                                <x-heroicon-o-arrow-up-right class="size-4 shrink-0" aria-hidden="true" />
                            </a>
                        @endif
                    </div>
                </div>

                <x-projects.artwork :project="$project" priority detail class="mt-10 sm:mt-14" />
            </div>
        </section>

        <section aria-labelledby="project-overview" class="py-12 sm:py-16">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] lg:gap-16 lg:px-8">
                <div>
                    <h2
                        id="project-overview"
                        class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white"
                    >
                        Project overview
                    </h2>
                    @if ($project->tech_stack)
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Built with</h3>
                            <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                @foreach ($project->tech_stack as $tech)
                                    <li>{{ $tech }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($project->tags->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Areas of focus</h3>
                            <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                @foreach ($project->tags as $tag)
                                    <li>{{ $tag->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="min-w-0">
                    @if ($project->content)
                        <x-prose class="prose-headings:font-semibold prose-h2:mt-10 prose-h2:text-2xl prose-h3:text-xl prose-p:leading-8 prose-pre:overflow-x-auto [&_h2:first-child]:mt-0 [&_img]:rounded-xl break-words">
                            {!!
                                Str::markdown($project->content, [
                                    'html_input' => 'strip',
                                    'allow_unsafe_links' => false,
                                ])
                            !!}
                        </x-prose>
                    @else
                        <p class="text-lg leading-8 text-gray-600 dark:text-gray-400">
                            The full project story is coming soon. Get in touch to discuss the problem, my contribution,
                            and how the work relates to what you’re building.
                        </p>
                    @endif
                </div>
            </div>
        </section>
    </article>

    <section
        aria-labelledby="project-contact-heading"
        class="dark:border-brand-800 dark:bg-brand-900/30 border-y border-gray-200 bg-gray-50 py-10 sm:py-14"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <h2
                    id="project-contact-heading"
                    class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white"
                >
                    Have a similar challenge?
                </h2>
                <p class="mt-3 text-base leading-7 text-gray-600 dark:text-gray-400">
                    Tell me what you’re building, what needs to change, or where you’re stuck.
                </p>
            </div>
            <a
                href="{{ route('contact') }}"
                class="focus-visible:outline-brand-500 w-fit shrink-0 rounded-lg bg-[#356d9f] px-5 py-3 text-sm font-semibold text-white hover:bg-[#2b5b87] focus-visible:outline-2 focus-visible:outline-offset-4"
            >Discuss a similar project</a>
        </div>
    </section>

    @if ($otherProjects->isNotEmpty())
        <section aria-labelledby="related-projects-heading" class="py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2
                    id="related-projects-heading"
                    class="mb-8 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white"
                >
                    More selected work
                </h2>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($otherProjects as $other)
                        <x-projects.related-card :project="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
