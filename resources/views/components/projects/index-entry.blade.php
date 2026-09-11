@props(['project', 'priority' => false])

<article data-project-entry class="grid gap-6 py-8 sm:gap-8 sm:py-12 lg:grid-cols-2 lg:items-center lg:gap-16">
    <a
        href="{{ route('projects.show', $project) }}"
        aria-label="Explore {{ $project->title }}"
        class="focus-visible:outline-brand-500 block min-w-0 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4"
    >
        <x-projects.artwork :project="$project" :priority="$priority" />
    </a>

    <div class="min-w-0">
        <h3 class="min-w-0 text-3xl font-semibold tracking-tight text-balance break-words text-gray-900 sm:text-4xl dark:text-white">
            <a
                href="{{ route('projects.show', $project) }}"
                class="hover:text-brand-700 focus-visible:outline-brand-500 dark:hover:text-brand-300 rounded focus-visible:outline-2 focus-visible:outline-offset-4"
            >{{ $project->title }}</a>
        </h3>
        <div class="mt-4 min-w-0">
            <p class="text-base leading-8 break-words text-gray-600 sm:text-lg dark:text-gray-300">
                {{ $project->description }}
            </p>
            @if ($project->tech_stack)
                <ul
                    aria-label="Technologies used for {{ $project->title }}"
                    class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-600 dark:text-gray-400"
                >
                    @foreach ($project->tech_stack as $tech)
                        <li class="break-all">{{ $tech }}</li>
                    @endforeach
                </ul>
            @endif
            <a
                href="{{ route('projects.show', $project) }}"
                class="text-brand-700 hover:text-brand-900 focus-visible:outline-brand-500 dark:text-brand-300 mt-5 inline-flex items-center gap-2 rounded py-2 text-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 dark:hover:text-white"
            >
                <span>Explore the project<span class="sr-only">: {{ $project->title }}</span></span>
                <x-heroicon-o-arrow-long-right class="size-5 shrink-0" aria-hidden="true" />
            </a>
        </div>
    </div>
</article>
