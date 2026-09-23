<x-layouts.site :seo-source="$seoSource ?? null" :structured-data="$structuredData ?? []">
    <div class="podcast-detail" style="--podcast-color: {{ $podcast->display_color }};">
        {{-- ===== EPISODE HERO ===== --}}
        <section class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
            <div class="relative mx-auto max-w-7xl px-4 py-10 sm:px-6 md:py-16 lg:px-8">
                {{-- Breadcrumb --}}
                <nav aria-label="Breadcrumb" class="relative z-10 mb-8 flex items-center gap-2 text-sm text-gray-500">
                    <a
                        href="{{ route('podcast.index') }}"
                        class="transition-colors hover:text-gray-900 dark:hover:text-white"
                    >Podcast</a>
                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <a
                        href="{{ route('podcast.show', $podcast) }}"
                        class="transition-colors hover:text-gray-900 dark:hover:text-white"
                    >{{ $podcast->name }}</a>
                    <svg aria-hidden="true" class="h-3.5 w-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span
                        aria-current="page"
                        class="font-mono text-xs text-gray-600 dark:text-gray-400"
                    >{{ \App\Presenters\EpisodePresenter::from($episode)->code() }}</span>
                </nav>

                <div class="relative z-10 flex flex-col items-center gap-8 lg:flex-row lg:gap-12">
                    {{-- Podcast artwork --}}
                    <div class="relative flex-shrink-0">
                        @if ($podcast->cover_image_url)
                            <x-podcast-cover
                                :podcast="$podcast"
                                sizes="224px"
                                width="224"
                                height="224"
                                priority
                                class="relative h-48 w-48 rounded-2xl object-cover shadow-2xl ring-1 ring-white/10 lg:h-56 lg:w-56"
                            />
                        @else
                            <div class="dark:border-surface-border dark:bg-surface-raised relative flex h-48 w-48 items-center justify-center rounded-2xl border border-gray-200 bg-gray-50 shadow-sm lg:h-56 lg:w-56">
                                <svg class="text-archive-link h-20 w-20 dark:text-[var(--podcast-color)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" /></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Episode info --}}
                    <div class="min-w-0 flex-1 text-center lg:text-left">
                        {{-- Meta badges --}}
                        <div class="mb-4 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                            <span class="text-archive-link rounded-lg bg-[var(--archive-link-alpha-08)] px-3 py-1.5 font-mono text-sm font-bold dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)] dark:text-[var(--podcast-color)]">{{ \App\Presenters\EpisodePresenter::from($episode)->code() }}</span>
                            @if ($episode->published_at)
                                <time
                                    datetime="{{ $episode->published_at->toDateString() }}"
                                    class="text-sm text-gray-500"
                                >{{ $episode->published_at->format('F d, Y') }}</time>
                            @else
                                <span class="text-sm text-gray-500">Draft preview</span>
                            @endif
                            @if (\App\Presenters\EpisodePresenter::from($episode)->duration())
                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ \App\Presenters\EpisodePresenter::from($episode)->duration() }}
                                </span>
                            @endif
                        </div>

                        <h1 class="mb-4 text-3xl leading-tight font-extrabold md:text-4xl lg:text-5xl">
                            {{ $episode->title }}
                        </h1>

                        @if ($episode->description)
                            <p class="mb-6 max-w-3xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
                                {{ $episode->description }}
                            </p>
                        @endif

                        {{-- Podcast name link --}}
                        <a
                            href="{{ route('podcast.show', $podcast) }}"
                            class="inline-flex items-center gap-2 text-sm text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                        >
                            @if ($podcast->cover_image_url)
                                <x-podcast-cover
                                    :podcast="$podcast"
                                    alt=""
                                    sizes="20px"
                                    width="20"
                                    height="20"
                                    class="h-5 w-5 rounded object-cover"
                                />
                            @else
                                <svg class="text-archive-link h-4 w-4 dark:text-[var(--podcast-color)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" /></svg>
                            @endif
                            {{ $podcast->name }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="dark:bg-surface-page bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
                <div class="flex flex-col gap-12 lg:flex-row">
                    {{-- Left Column --}}
                    <div class="min-w-0 flex-1">
                        {{-- Custom Audio Player --}}
                        @if ($audioUrl)
                            <div
                                class="group/player dark:border-surface-border dark:bg-surface-control mb-10 overflow-hidden rounded-2xl border border-gray-200 bg-white"
                                data-audio-player
                                x-data="audioPlayer"
                                x-bind:data-playing="playingAttribute"
                                data-playing="false"
                            >
                                <audio
                                    controls
                                    data-audio
                                    preload="metadata"
                                    x-ref="audio"
                                    x-on:loadedmetadata="updateProgress"
                                    x-on:durationchange="updateProgress"
                                    x-on:timeupdate="updateProgress"
                                    x-on:play="updatePlaybackState"
                                    x-on:pause="updatePlaybackState"
                                    x-on:ended="updatePlaybackState"
                                >
                                    <source src="{{ $audioUrl }}" type="audio/mpeg" />
                                </audio>

                                {{-- Waveform visualization --}}
                                <div class="px-6 pt-6 pb-2">
                                    <div
                                        class="flex h-16 items-end justify-center gap-[2px] opacity-40"
                                        aria-hidden="true"
                                    >
                                        @for ($i = 0; $i < 80; $i++)
                                            <div
                                                class="h-full w-[3px] origin-bottom animate-[podcast-player-waveform_var(--dur)_ease-in-out_infinite_alternate] rounded-full bg-[var(--podcast-color)] [animation-play-state:paused] group-data-[playing=true]/player:[animation-play-state:running] motion-reduce:animate-none"
                                                style="--from: {{ (10 + (($i * 7) % 21)) / 100 }}; --to: {{ (40 + (($i * 13) % 61)) / 100 }}; --dur: {{ (4 + (($i * 5) % 9)) / 10 }}s; animation-delay: {{ $i * 0.04 }}s;"
                                            ></div>
                                        @endfor
                                    </div>
                                </div>

                                {{-- Progress bar --}}
                                <div class="px-6 py-2">
                                    <div class="group relative h-5">
                                        <div class="bg-surface-border pointer-events-none absolute inset-x-0 top-1/2 h-1.5 -translate-y-1/2 rounded-full">
                                            <div
                                                class="absolute inset-y-0 left-0 w-0 rounded-full bg-[var(--podcast-color)]"
                                                data-audio-progress
                                                x-bind:style="progressStyle"
                                            ></div>
                                        </div>
                                        <input
                                            type="range"
                                            min="0"
                                            max="100"
                                            step="0.1"
                                            value="0"
                                            data-audio-seek
                                            x-bind:value="percentage"
                                            x-bind:aria-valuetext="seekLabel"
                                            x-on:input="seek"
                                            aria-label="Seek episode"
                                            aria-valuetext="0:00 of 0:00"
                                            class="absolute inset-0 h-5 w-full cursor-pointer opacity-0"
                                        />
                                    </div>
                                    <div class="mt-2 flex justify-between font-mono text-xs text-gray-600">
                                        <span data-audio-current-time x-text="elapsedLabel">0:00</span>
                                        <span data-audio-duration x-text="durationLabel">0:00</span>
                                    </div>
                                </div>

                                {{-- Controls --}}
                                <div class="flex items-center justify-between px-6 pb-6">
                                    <div class="flex items-center gap-3">
                                        {{-- Podcast mini artwork --}}
                                        @if ($podcast->cover_image_url)
                                            <x-podcast-cover
                                                :podcast="$podcast"
                                                alt=""
                                                sizes="40px"
                                                width="40"
                                                height="40"
                                                class="h-10 w-10 rounded-lg object-cover"
                                            />
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $episode->title }}
                                            </p>
                                            <p class="truncate text-xs text-gray-500">{{ $podcast->name }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        {{-- Skip back 15s --}}
                                        <button
                                            type="button"
                                            data-audio-skip-back
                                            x-on:click="skipBack"
                                            aria-label="Skip back 15 seconds"
                                            class="relative inline-flex size-11 shrink-0 touch-manipulation items-center justify-center text-gray-500 transition-colors hover:text-gray-900 dark:hover:text-white"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" /></svg>
                                            <span class="text-meta absolute -bottom-3.5 left-1/2 -translate-x-1/2 font-mono text-gray-600">15</span>
                                        </button>

                                        {{-- Play/Pause --}}
                                        <button
                                            type="button"
                                            data-audio-play
                                            x-on:click="togglePlayback"
                                            x-bind:aria-label="playLabel"
                                            x-bind:aria-pressed="playing"
                                            aria-label="Play episode"
                                            aria-pressed="false"
                                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--podcast-color)] text-white transition-transform hover:scale-105"
                                        >
                                            <svg
                                                data-audio-play-icon
                                                x-bind:hidden="playing"
                                                aria-hidden="true"
                                                class="ml-0.5 h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            ><path d="M8 5v14l11-7z" /></svg>
                                            <svg
                                                data-audio-pause-icon
                                                x-bind:hidden="paused"
                                                hidden
                                                aria-hidden="true"
                                                class="h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            ><path d="M6 4h4v16H6zM14 4h4v16h-4z" /></svg>
                                        </button>

                                        {{-- Skip forward 30s --}}
                                        <button
                                            type="button"
                                            data-audio-skip-forward
                                            x-on:click="skipForward"
                                            aria-label="Skip forward 30 seconds"
                                            class="relative inline-flex size-11 shrink-0 touch-manipulation items-center justify-center text-gray-500 transition-colors hover:text-gray-900 dark:hover:text-white"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z" /></svg>
                                            <span class="text-meta absolute -bottom-3.5 left-1/2 -translate-x-1/2 font-mono text-gray-600">30</span>
                                        </button>
                                    </div>

                                    {{-- Speed control --}}
                                    <div>
                                        <button
                                            type="button"
                                            data-audio-speed
                                            x-on:click="cycleSpeed"
                                            x-bind:aria-label="speedDescription"
                                            aria-label="Playback speed 1 times. Activate to change."
                                            class="dark:border-surface-border min-h-11 min-w-11 touch-manipulation rounded-lg border border-gray-200 px-2.5 py-1 font-mono text-xs text-gray-600 transition-colors hover:border-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                        >
                                            <span data-audio-speed-label x-text="speedLabel">1x</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Supported podcast embeds --}}
                        @if ($embedUrl)
                            <div class="dark:border-surface-border mb-10 overflow-hidden rounded-2xl border border-gray-200">
                                <iframe
                                    src="{{ $embedUrl }}"
                                    title="{{ $episode->title }} podcast player"
                                    class="h-[232px] w-full border-0"
                                    loading="lazy"
                                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @endif

                        {{-- Description Fallback (no audio, no show_notes, no transcript, no youtube, no embed) --}}
                        @if (! $audioUrl && ! $episode->show_notes && ! $episode->transcript && ! ($episode->youtube_url && str_contains($episode->youtube_url, 'youtu')) && ! $embedUrl)
                            <div class="dark:border-surface-border dark:bg-surface-control relative mb-10 rounded-2xl border border-gray-200 bg-white p-8">
                                <div class="text-archive-link absolute top-6 left-6 text-6xl leading-none opacity-15 dark:text-[var(--podcast-color)]">
                                    "
                                </div>
                                <div class="pt-4 pl-8">
                                    <p class="text-xl leading-relaxed text-gray-700 italic md:text-2xl dark:text-gray-300">
                                        {{ $episode->description }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- YouTube Embed --}}
                        @if ($episode->youtube_url && str_contains($episode->youtube_url, 'youtu'))
                            <div class="dark:border-surface-border mb-10 overflow-hidden rounded-2xl border border-gray-200">
                                @php
                                    preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $episode->youtube_url, $matches);
                                    $videoId = $matches[1] ?? null;
                                @endphp
                                @if ($videoId)
                                    <div
                                        class="bg-surface-page relative aspect-video w-full"
                                        data-youtube-facade
                                        x-data="youtubePlayer"
                                    >
                                        <template data-youtube-player x-if="loaded">
                                            <iframe
                                                src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1"
                                                title="{{ $episode->title }} on YouTube"
                                                class="absolute inset-0 h-full w-full border-0"
                                                allow="autoplay; encrypted-media; picture-in-picture"
                                                referrerpolicy="strict-origin-when-cross-origin"
                                                allowfullscreen
                                            ></iframe>
                                        </template>

                                        <button
                                            type="button"
                                            data-youtube-play
                                            disabled
                                            x-bind:disabled="false"
                                            x-on:click="load"
                                            x-bind:hidden="loaded"
                                            aria-label="Play {{ $episode->title }} on YouTube"
                                            class="group focus-visible:outline-brand-400 absolute inset-0 flex w-full flex-col items-center justify-center gap-4 px-6 text-center text-white transition-colors hover:bg-white/[0.03] focus-visible:outline-2 focus-visible:outline-offset-[-4px]"
                                        >
                                            <span
                                                class="flex h-16 w-16 items-center justify-center rounded-full border border-white/20 bg-white/10 transition-transform group-hover:scale-105"
                                                aria-hidden="true"
                                            >
                                                <svg class="ml-1 h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                            </span>
                                            <span>
                                                <span class="text-brand-300 tracking-label block font-mono text-xs uppercase">Video episode</span>
                                                <span class="mt-2 block text-lg font-semibold">Watch on YouTube</span>
                                            </span>
                                        </button>

                                        <noscript>
                                            <a
                                                href="{{ $episode->youtube_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="decoration-brand-400 absolute inset-0 flex items-center justify-center text-base font-semibold text-white underline underline-offset-4"
                                            >Watch on YouTube</a>
                                        </noscript>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <x-podcast.platform-links :embed-link="$embedLink" :youtube-url="$episode->youtube_url" />
                        <x-podcast.guest-card :episode="$episode" />

                        {{-- Show Notes --}}
                        @if ($episode->show_notes)
                            <x-podcast.show-notes :content="$episode->show_notes" />
                        @endif

                        {{-- Transcript --}}
                        @if ($episode->transcript)
                            <x-podcast.transcript :content="$episode->transcript" />
                        @endif

                        {{-- Tags --}}
                        @if ($episode->tags->count())
                            <div class="mb-12">
                                <h3 class="mb-3 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                                    Topics
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($episode->tags as $tag)
                                        <span class="dark:border-surface-border rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition-colors hover:border-gray-600 dark:text-gray-400">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Empty State Fallback --}}
                        @if (! $audioUrl && ! $episode->show_notes && ! $episode->transcript && ! $episode->guest_name && ! $episode->tags->count() && ! ($episode->youtube_url && str_contains($episode->youtube_url, 'youtu')) && ! $embedLink)
                            <div class="dark:border-surface-border mb-12 rounded-2xl border border-dashed border-gray-200 p-8 text-center">
                                <svg class="mx-auto mb-4 h-12 w-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-lg font-semibold text-gray-600 dark:text-gray-400">
                                    Full episode details coming soon
                                </p>
                                <p class="mt-2 text-sm text-gray-600">
                                    Show notes, links, and more will be added shortly.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Right Sidebar --}}
                    <div class="flex-shrink-0 lg:w-80">
                        <div class="space-y-6 lg:sticky lg:top-8">
                            {{-- About This Podcast --}}
                            <div class="dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 bg-white p-5">
                                <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                                    About This Podcast
                                </h3>
                                <a href="{{ route('podcast.show', $podcast) }}" class="group block">
                                    <div class="mb-3 flex items-center gap-3">
                                        @if ($podcast->cover_image_url)
                                            <x-podcast-cover
                                                :podcast="$podcast"
                                                sizes="48px"
                                                width="48"
                                                height="48"
                                                class="h-12 w-12 rounded-lg object-cover"
                                            />
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[color-mix(in_srgb,var(--podcast-color)_6%,transparent)]">
                                                <svg class="text-archive-link h-6 w-6 dark:text-[var(--podcast-color)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2H3v2a9 9 0 0 0 8 8.94V23h2v-2.06A9 9 0 0 0 21 12v-2h-2z" /></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold transition-opacity group-hover:opacity-80">
                                                {{ $podcast->name }}
                                            </p>
                                            <p class="text-xs text-gray-500">View all episodes →</p>
                                        </div>
                                    </div>
                                </a>
                                <p class="text-xs leading-relaxed text-gray-500">
                                    {{ Str::limit($podcast->description, 150) }}
                                </p>
                            </div>

                            {{-- Episode Details --}}
                            <div class="dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 bg-white p-5">
                                <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                                    Episode Details
                                </h3>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Episode</dt>
                                        <dd class="text-archive-link font-mono font-semibold dark:text-[var(--podcast-color)]">
                                            {{ \App\Presenters\EpisodePresenter::from($episode)->code() }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Published</dt>
                                        <dd class="text-gray-700 dark:text-gray-300">
                                            @if ($episode->published_at)
                                                <time datetime="{{ $episode->published_at->toDateString() }}">{{ $episode->published_at->format('M d, Y') }}</time>
                                            @else
                                                Draft preview
                                            @endif
                                        </dd>
                                    </div>
                                    @if (\App\Presenters\EpisodePresenter::from($episode)->duration())
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Duration</dt>
                                            <dd class="text-gray-700 dark:text-gray-300">
                                                {{ \App\Presenters\EpisodePresenter::from($episode)->duration() }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if ($episode->season_number)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Season</dt>
                                            <dd class="text-gray-700 dark:text-gray-300">
                                                {{ $episode->season_number }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if ($episode->guest_name)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Guest</dt>
                                            <dd class="text-gray-700 dark:text-gray-300">{{ $episode->guest_name }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            {{-- Share Episode --}}
                            <div class="dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 bg-white p-5">
                                <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                                    Share Episode
                                </h3>
                                <div class="flex gap-2">
                                    <a
                                        href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — ' . $podcast->name) }}&url={{ urlencode(route('podcast.episode', [$podcast, $episode])) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="Share {{ $episode->title }} on X"
                                        class="dark:border-surface-border flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 py-2.5 text-sm text-gray-600 transition-colors hover:-translate-y-0.5 hover:border-gray-600 hover:text-gray-900 motion-reduce:transition-none dark:text-gray-400 dark:hover:text-white"
                                    >
                                        <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                                    </a>
                                    <a
                                        href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('podcast.episode', [$podcast, $episode])) }}&title={{ urlencode($episode->title) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="Share {{ $episode->title }} on LinkedIn"
                                        class="dark:border-surface-border flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 py-2.5 text-sm text-gray-600 transition-colors hover:-translate-y-0.5 hover:border-gray-600 hover:text-gray-900 motion-reduce:transition-none dark:text-gray-400 dark:hover:text-white"
                                    >
                                        <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" /></svg>
                                    </a>
                                    <x-copy-button
                                        label="Copy episode link"
                                        success="Episode link copied"
                                        :text="route('podcast.episode', [$podcast, $episode])"
                                        data-podcast-copy-url="{{ route('podcast.episode', [$podcast, $episode]) }}"
                                        class="dark:border-surface-border flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-200 py-2.5 text-sm text-gray-600 transition-colors hover:-translate-y-0.5 hover:border-gray-600 hover:text-gray-900 motion-reduce:transition-none dark:text-gray-400 dark:hover:text-white"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-podcast.episode-navigation :podcast="$podcast" :previous="$prevEpisode" :next="$nextEpisode" />
            </div>
        </div>
    </div>
</x-layouts.site>
