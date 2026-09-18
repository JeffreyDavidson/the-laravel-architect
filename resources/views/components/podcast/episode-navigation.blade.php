@props(['podcast', 'previous' => null, 'next' => null])

<div class="dark:border-surface-border mt-16 border-t border-gray-200 pt-8">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @if ($previous)
            <a
                href="{{ route('podcast.episode', [$podcast, $previous]) }}"
                class="ep-nav group dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 p-5 transition-all duration-300 hover:bg-white"
            >
                <div class="flex items-center gap-3">
                    <svg class="ep-nav-arrow h-5 w-5 flex-shrink-0 text-gray-600 transition-transform [--arrow-dir:-4px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    <div class="min-w-0">
                        <span class="text-xs tracking-wide text-gray-500 uppercase">Previous Episode</span>
                        <p class="mt-0.5 truncate font-semibold transition-opacity group-hover:opacity-80">
                            {{ $previous->title }}
                        </p>
                        <span class="font-mono text-xs text-gray-500">
                            {{ \App\Presenters\EpisodePresenter::from($previous)->code() }}
                            @if (\App\Presenters\EpisodePresenter::from($previous)->duration()) ·{{ \App\Presenters\EpisodePresenter::from($previous)->duration() }}@endif
                        </span>
                    </div>
                </div>
            </a>
        @else
            <div></div>
        @endif

        @if ($next)
            <a
                href="{{ route('podcast.episode', [$podcast, $next]) }}"
                class="ep-nav group dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 p-5 text-right transition-all duration-300 hover:bg-white"
            >
                <div class="flex items-center justify-end gap-3">
                    <div class="min-w-0">
                        <span class="text-xs tracking-wide text-gray-500 uppercase">Next Episode</span>
                        <p class="mt-0.5 truncate font-semibold transition-opacity group-hover:opacity-80">
                            {{ $next->title }}
                        </p>
                        <span class="font-mono text-xs text-gray-500">
                            {{ \App\Presenters\EpisodePresenter::from($next)->code() }}
                            @if (\App\Presenters\EpisodePresenter::from($next)->duration()) ·{{ \App\Presenters\EpisodePresenter::from($next)->duration() }}@endif
                        </span>
                    </div>
                    <svg class="ep-nav-arrow h-5 w-5 flex-shrink-0 text-gray-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </div>
            </a>
        @else
            <div></div>
        @endif
    </div>
</div>
