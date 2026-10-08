<x-layouts.site :seo-source="$seoSource ?? null" :structured-data="$structuredData ?? []">
    <div
        class="podcast-detail"
        style="--podcast-color: {{ \App\Presenters\PodcastPresenter::from($podcast)->displayColor() }};"
    >
        {{-- ===== PODCAST HERO ===== --}}
        <x-page-header>
            {{-- Breadcrumb --}}
            <a
                href="{{ route('podcast.index') }}"
                class="relative z-10 mb-8 inline-flex items-center gap-1.5 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Podcast
            </a>

            <div class="relative z-10 flex flex-col items-center gap-10 md:flex-row">
                {{-- Artwork --}}
                <div class="relative flex-shrink-0">
                    <x-podcast-cover
                        :podcast="$podcast"
                        sizes="224px"
                        width="224"
                        height="224"
                        priority
                        class="relative h-48 w-48 rounded-2xl object-cover shadow-2xl ring-1 ring-white/10 md:h-56 md:w-56"
                    >
                        <x-slot:placeholder>
                            <div class="dark:border-surface-border dark:bg-surface-raised relative flex h-48 w-48 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 shadow-sm md:h-56 md:w-56">
                                <x-svg-icon
                                    name="microphone"
                                    class="text-archive-link h-20 w-20 dark:text-[var(--podcast-color)]"
                                />
                            </div>
                        </x-slot:placeholder>
                    </x-podcast-cover>
                </div>

                {{-- Info --}}
                <div class="flex-1 text-center md:text-left">
                    {{-- Badge --}}
                    <div class="mb-4 flex items-center justify-center gap-3 md:justify-start">
                        @if ($episodes->count())
                            <span class="text-archive-link inline-flex items-center gap-1.5 rounded-full bg-[var(--archive-link-alpha-08)] px-3 py-1 text-xs font-semibold dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)]">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                {{ $episodes->total() }} {{ Str::plural('Episode', $episodes->total()) }}
                            </span>
                        @else
                            <span class="text-archive-link inline-flex items-center gap-1.5 rounded-full bg-[var(--archive-link-alpha-08)] px-3 py-1 text-xs font-semibold tracking-wide uppercase dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[var(--podcast-color)]"></span>
                                Coming Soon
                            </span>
                        @endif

                        {{-- Mini equalizer --}}
                        <x-podcast.equalizer />
                    </div>

                    <h1 class="mb-4 text-4xl leading-tight font-extrabold text-gray-900 md:text-5xl dark:text-white">
                        {{ $podcast->name }}
                    </h1>
                    <p class="mb-8 max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
                        {{ $podcast->description }}
                    </p>

                    {{-- Subscribe buttons --}}
                    <x-podcast.platform-links :podcast="$podcast" />
                </div>
            </div>
        </x-page-header>

        {{-- ===== ABOUT ===== --}}
        @if ($podcast->long_description)
            <section class="dark:border-surface-border border-b border-gray-200">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        <h2 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                            About the Show
                        </h2>
                        <div class="space-y-4 text-base leading-relaxed text-gray-600 dark:text-gray-400">
                            @foreach (explode("\n\n", $podcast->long_description) as $paragraph)
                                @if (trim($paragraph))
                                    <p>{{ trim($paragraph) }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ===== LATEST EPISODE FEATURE ===== --}}
        @if ($latestEpisode)
            <section class="dark:border-surface-border border-b border-gray-200">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <a
                        href="{{ route('podcast.episode', [$podcast, $latestEpisode]) }}"
                        class="group hover:border-brand-600/50 dark:border-surface-border dark:bg-surface-control block overflow-hidden rounded-2xl border border-gray-200 bg-white transition-colors"
                    >
                        {{-- Top accent --}}
                        <div class="h-[2px] bg-[var(--podcast-color)]"></div>

                        <div class="p-8 md:p-10">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="text-archive-link rounded-full bg-[var(--archive-link-alpha-08)] px-3 py-1 text-xs font-semibold tracking-wide uppercase dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)] dark:text-[var(--podcast-color)]">Latest Episode</span>
                                <x-podcast.episode-meta
                                    :episode="$latestEpisode"
                                    code-class="font-mono text-sm text-gray-500"
                                    separator-class="text-sm text-gray-600"
                                    item-class="text-sm text-gray-500"
                                />
                            </div>

                            <h2 class="mb-3 text-2xl font-extrabold text-gray-900 transition-opacity group-hover:opacity-80 md:text-3xl dark:text-white">
                                {{ $latestEpisode->title }}
                            </h2>

                            @if ($latestEpisode->description)
                                <p class="mb-6 max-w-3xl leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ Str::limit($latestEpisode->description, 300) }}
                                </p>
                            @endif

                            @if ($latestEpisode->guest_name)
                                <div class="mb-6 flex items-center gap-3">
                                    <div class="text-archive-link flex h-8 w-8 items-center justify-center rounded-full bg-[var(--archive-link-alpha-08)] text-xs font-bold dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)] dark:text-[var(--podcast-color)]">
                                        {{ substr($latestEpisode->guest_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $latestEpisode->guest_name }}</span>
                                        @if ($latestEpisode->guest_title)
                                            <span class="text-sm text-gray-600">
                                                · {{ $latestEpisode->guest_title }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="text-archive-link inline-flex items-center gap-2 text-sm font-semibold dark:text-[var(--podcast-color)]">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--podcast-color)] transition-transform group-hover:scale-110">
                                    <svg class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                </div>
                                <span>Listen Now</span>
                            </div>
                        </div>
                    </a>
                </div>
            </section>
        @endif

        {{-- ===== ALL EPISODES ===== --}}
        <section class="dark:bg-surface-page bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
                <div class="mb-8 flex items-center justify-between">
                    <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        @if ($episodes->count()) All Episodes @else Episodes @endif
                    </h2>
                    @if ($episodes->total() > 0)
                        <span class="font-mono text-sm text-gray-500">{{ $episodes->total() }} total</span>
                    @endif
                </div>

                @if ($episodes->count())
                    <div class="space-y-2">
                        @foreach ($episodes as $episode)
                            <a
                                href="{{ route('podcast.episode', [$podcast, $episode]) }}"
                                class="group dark:border-surface-border dark:bg-surface-control/50 flex items-center gap-5 rounded-xl border border-gray-200 bg-white p-4 transition-[background-color,transform] duration-300 hover:-translate-y-0.5 hover:bg-[var(--white-02)] motion-reduce:transition-none md:p-5 dark:hover:bg-[var(--white-02)]"
                            >
                                {{-- Episode number / play icon --}}
                                <div class="relative flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--podcast-color)_3%,transparent)]">
                                    <span class="text-archive-link font-mono text-xs font-bold transition-opacity duration-200 group-hover:opacity-0 motion-reduce:transition-none dark:text-[var(--podcast-color)]">{{ \App\Presenters\EpisodePresenter::from($episode)->code() }}</span>
                                    <div class="absolute inset-0 flex scale-80 items-center justify-center opacity-0 transition-[opacity,scale] duration-200 group-hover:scale-100 group-hover:opacity-100 motion-reduce:transition-none">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--podcast-color)]">
                                            <svg class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-semibold text-gray-900 transition-opacity group-hover:opacity-80 dark:text-white">
                                        {{ $episode->title }}
                                    </h3>
                                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                                        <x-podcast.episode-meta
                                            :episode="$episode"
                                            separator-class="text-gray-300 dark:text-gray-700"
                                        />
                                        @if ($episode->guest_name)
                                            <span class="hidden text-gray-300 sm:inline dark:text-gray-700">·</span>
                                            <span class="hidden sm:inline"
                                                >with
                                                <span
                                                    class="text-archive-link dark:text-[var(--podcast-color)]"
                                                    >{{ $episode->guest_name }}</span
                                                ></span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Mini equalizer on hover --}}
                                <x-podcast.equalizer variant="row" />

                                <svg class="h-5 w-5 flex-shrink-0 text-gray-600 transition-transform group-hover:translate-x-1 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">{{ $episodes->links() }}</div>
                @else
                    <div class="dark:border-surface-border rounded-2xl border border-dashed border-gray-200 py-20 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-[color-mix(in_srgb,var(--podcast-color)_6%,transparent)]">
                            <x-svg-icon
                                name="microphone"
                                class="text-archive-link h-8 w-8 dark:text-[var(--podcast-color)]"
                            />
                        </div>
                        <p class="mb-2 text-lg font-medium text-gray-600 dark:text-gray-400">No episodes yet</p>
                        <p class="text-sm text-gray-500">First episodes are in the works. Check back soon!</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.site>
