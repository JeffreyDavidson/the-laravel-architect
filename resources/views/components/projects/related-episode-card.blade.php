@props(['episode'])

<a
    href="{{ route('podcast.episode', [$episode->podcast, $episode]) }}"
    class="group focus-visible:outline-brand-500 dark:border-brand-800 flex h-full min-w-0 flex-col border-t border-gray-200 py-6 focus-visible:outline-2 focus-visible:outline-offset-4"
>
    <div class="flex items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
        <span class="font-mono font-semibold uppercase">{{ $episode->podcast->name }}</span>
        <span>{{ \App\Presenters\EpisodePresenter::from($episode)->code() }}</span>
    </div>
    <h3 class="group-hover:text-brand-700 dark:group-hover:text-brand-300 mt-4 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
        {{ $episode->title }}
    </h3>
    @if ($episode->description)
        <p class="mt-3 line-clamp-3 text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $episode->description }}</p>
    @endif
    <span class="text-brand-700 dark:text-brand-300 mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold">
        Listen to the episode
        <x-heroicon-o-arrow-long-right class="size-5 shrink-0" aria-hidden="true" />
    </span>
</a>
