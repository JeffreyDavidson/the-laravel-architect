<x-layouts.site :page-meta="$pageMeta">
    {{-- The reveal and count-up animations cover the whole page, so their component wraps every section. --}}
    <div data-home-reveal x-data="homeReveal">
        {{-- ===== HERO ===== --}}
        <section
            data-home-hero
            class="border-surface-border-strong bg-surface-control after:bg-surface-hero/60 md:after:bg-surface-hero/12 relative isolate overflow-hidden border-b text-white after:pointer-events-none after:absolute after:inset-0 after:-z-10"
        >
            <div class="absolute inset-x-0 top-16 bottom-0 -z-20 overflow-hidden md:inset-0">
                <picture
                    class="[&_img]:h-full [&_img]:w-full [&_img]:object-cover [&_img]:object-bottom md:[&_img]:object-center absolute inset-0 block h-full w-full"
                    aria-hidden="true"
                >
                    <source
                        media="(max-width: 767px)"
                        srcset="{{ Vite::asset('resources/images/home-hero-mobile-640.webp') }} 640w, {{ Vite::asset('resources/images/home-hero-mobile-1024.webp') }} 1024w"
                        sizes="100vw"
                    />
                    <source
                        srcset="{{ Vite::asset('resources/images/home-hero-desktop-1024.webp') }} 1024w, {{ Vite::asset('resources/images/home-hero-desktop-1536.webp') }} 1536w"
                        sizes="100vw"
                    />
                    <img
                        src="{{ Vite::asset('resources/images/home-hero-desktop-1536.webp') }}"
                        alt=""
                        width="1536"
                        height="1024"
                        fetchpriority="high"
                        decoding="async"
                    />
                </picture>

                <svg
                    class="pointer-events-none absolute inset-0 h-full w-full"
                    viewBox="0 0 1536 1024"
                    preserveAspectRatio="xMidYMid slice"
                    aria-hidden="true"
                >
                    <g class="hidden md:block">
                        <circle class="origin-center animate-[heroBlueprintPulse_7s_ease-out_infinite] fill-none stroke-[var(--hero-blueprint)] stroke-2 opacity-0 [transform-box:fill-box] motion-reduce:animate-none" cx="530" cy="535" r="54" />
                        <circle class="origin-center animate-[heroNodePulse_4.5s_ease-out_infinite] fill-none stroke-[var(--hero-node)] stroke-[3] opacity-0 [transform-box:fill-box] motion-reduce:animate-none" cx="58" cy="560" r="24" />
                        <path class="pointer-events-none fill-none stroke-[var(--hero-track)] stroke-[3] [stroke-linecap:round]" d="M58 560 H190" />
                        <path class="pointer-events-none animate-[heroSignalTravel_4.5s_linear_infinite] fill-none stroke-[var(--hero-travel)] stroke-[4] [stroke-dasharray:10_122] [stroke-linecap:round] motion-reduce:animate-none motion-reduce:opacity-0" d="M58 560 H190" />
                        <path class="pointer-events-none animate-[heroGridSweep_8s_ease-in-out_infinite] fill-none stroke-[var(--hero-sweep)] stroke-2 opacity-0 [stroke-dasharray:36_330] [stroke-linecap:round] motion-reduce:animate-none" d="M150 710 H520" />
                        <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M820 692 C780 660 852 638 820 604 C795 577 830 556 818 526" />
                        <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [animation-delay:1.4s] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M850 690 C824 657 886 636 856 604 C832 578 872 550 853 522" />
                        <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [animation-delay:2.8s] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M884 690 C858 658 916 642 890 612 C866 585 906 562 889 538" />
                    </g>
                </svg>

                <svg
                    class="pointer-events-none absolute inset-0 block h-full w-full md:hidden"
                    viewBox="0 0 1024 1280"
                    preserveAspectRatio="xMidYMid slice"
                    aria-hidden="true"
                >
                    <circle class="origin-center animate-[heroNodePulse_4.5s_ease-out_infinite] fill-none stroke-[var(--hero-node)] stroke-[3] opacity-0 [transform-box:fill-box] motion-reduce:animate-none" cx="48" cy="1038" r="24" />
                    <path class="pointer-events-none fill-none stroke-[var(--hero-track)] stroke-[3] [stroke-linecap:round]" d="M48 1038 H260" />
                    <path class="pointer-events-none animate-[heroSignalTravel_4.5s_linear_infinite] fill-none stroke-[var(--hero-travel)] stroke-[4] [stroke-dasharray:10_122] [stroke-linecap:round] motion-reduce:animate-none motion-reduce:opacity-0" d="M48 1038 H260" />
                    <path class="pointer-events-none animate-[heroGridSweep_8s_ease-in-out_infinite] fill-none stroke-[var(--hero-sweep)] stroke-2 opacity-0 [stroke-dasharray:36_330] [stroke-linecap:round] motion-reduce:animate-none" d="M80 1130 H430" />
                    <circle class="origin-center animate-[heroBlueprintPulse_7s_ease-out_infinite] fill-none stroke-[var(--hero-blueprint)] stroke-2 opacity-0 [transform-box:fill-box] motion-reduce:animate-none" cx="120" cy="760" r="56" />
                    <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M518 974 C480 940 548 914 520 878 C494 846 536 818 522 786" />
                    <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [animation-delay:1.4s] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M546 974 C518 940 578 914 550 878 C526 847 566 820 548 790" />
                    <path class="origin-bottom animate-[heroSteam_5s_ease-in-out_infinite] fill-none stroke-[var(--hero-steam)] stroke-[6] opacity-0 [filter:blur(0.9px)_drop-shadow(0_0_5px_var(--hero-steam-shadow))] [animation-delay:2.8s] [stroke-linecap:round] [transform-box:fill-box] motion-reduce:animate-none" d="M574 974 C550 942 608 920 580 887 C556 858 598 832 580 806" />
                </svg>
            </div>

            <div class="mx-auto flex min-h-[calc(100svh-4rem)] max-w-7xl items-start px-4 py-16 pt-12 sm:min-h-[calc(100svh-4.5rem)] sm:items-center sm:px-6 md:pt-16 lg:px-8">
                <div class="max-w-2xl py-4 [text-shadow:0_2px_24px_rgb(0_0_0/0.28)]">
                    <p class="text-brand-200 font-mono text-xs leading-normal font-semibold tracking-[0.12em] uppercase">
                        Jeffrey Davidson · The Laravel Architect
                    </p>

                    <h1 class="mt-5 text-5xl leading-[1.02] font-semibold tracking-[-0.045em] text-white sm:text-6xl xl:text-[4.5rem]">
                        Laravel systems,<br />
                        <span class="text-brand-300">easier to change.</span>
                    </h1>

                    <p class="mt-6 max-w-lg text-lg leading-8 text-slate-300">
                        Architecture, modernization, and hands-on development for teams carrying real production
                        complexity.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
                        <x-button
                            href="{{ route('contact.create') }}"
                            class="focus-visible:outline-brand-200 min-h-11 rounded-lg border border-transparent px-[1.15rem] py-[0.7rem] text-[0.9375rem] font-semibold focus-visible:outline-offset-3"
                        >Discuss a Project</x-button>
                        <a
                            href="{{ route('projects.index') }}"
                            class="bg-surface-page/60 hover:bg-brand-900 focus-visible:outline-brand-200 text-brand-50 hover:border-brand-300 inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-500 px-[1.15rem] py-[0.7rem] text-[0.9375rem] font-semibold transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-3"
                        >View Projects</a>
                    </div>
                </div>
            </div>
        </section>

        <x-home.proof-strip
            years="15"
            :published-posts="$publishedPostCount"
            :published-projects="$publishedProjectCount"
        />

        <x-home.services />

        <x-home.selected-work :projects="$featuredProjects" />

        {{-- ===== LATEST POSTS ===== --}}
        <section class="dark:border-brand-800/50 border-t border-gray-200 bg-gray-50 py-14 sm:py-24 dark:bg-transparent">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if ($latestPosts->count())
                    <x-home.section-header
                        title="Latest writing"
                        :href="route('blog.index')"
                        link-label="Browse all articles"
                    />
                    <div class="grid gap-6">
                        {{-- Featured post --}}
                        @if ($featuredPost)
                            <x-blog-card
                                :post="$featuredPost"
                                variant="featured"
                                data-reveal=""
                                class="data-[reveal=pending]:translate-y-3 data-[reveal=pending]:opacity-0 motion-safe:data-[reveal]:transition-[opacity,transform,translate] motion-safe:data-[reveal]:duration-450 motion-safe:data-[reveal]:ease-[ease]"
                            />
                        @endif

                        {{-- Remaining posts --}}
                        @if ($latestPosts->count() > 1)
                            <div class="grid gap-6 md:grid-cols-2">
                                @foreach ($latestPosts->skip(1) as $post)
                                    <x-blog-card
                                        :post="$post"
                                        variant="compact"
                                        data-reveal=""
                                        class="data-[reveal=pending]:translate-y-3 data-[reveal=pending]:opacity-0 motion-safe:data-[reveal]:transition-[opacity,transform,translate] motion-safe:data-[reveal]:duration-450 motion-safe:data-[reveal]:ease-[ease]"
                                    />
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div
                    data-reveal
                    class="mt-12 data-[reveal=pending]:translate-y-3 data-[reveal=pending]:opacity-0 motion-safe:data-[reveal]:transition-[opacity,transform,translate] motion-safe:data-[reveal]:duration-450 motion-safe:data-[reveal]:ease-[ease] sm:mt-16"
                >
                    <x-home.newsletter-signup class="mx-auto max-w-3xl text-center" />
                </div>
            </div>
        </section>

        {{-- ===== MEDIA ===== --}}
        <section class="dark:border-brand-800/50 border-t border-gray-200 bg-white py-14 sm:py-24 dark:bg-transparent">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-home.section-header title="Away from the editor" />

                <div class="grid items-center gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <a
                        href="{{ route('podcasts.index') }}"
                        class="group focus-visible:outline-brand-500 dark:border-brand-700 dark:bg-surface-elevated grid items-center overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-4 md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]"
                    >
                        @if ($podcast)
                            <div class="[&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:w-full [&_img]:max-w-64 md:[&_img]:max-w-none block bg-black">
                                <x-podcast-cover
                                    :podcast="$podcast"
                                    sizes="(min-width: 768px) 280px, 160px"
                                    width="512"
                                    height="512"
                                    alt=""
                                />
                            </div>
                        @else
                            <picture class="[&_img]:mx-auto [&_img]:block [&_img]:h-auto [&_img]:w-full [&_img]:max-w-64 md:[&_img]:max-w-none block bg-black">
                                <source
                                    type="image/webp"
                                    srcset="{{ Vite::asset('resources/images/podcast-coffee-logo-320.webp') }} 320w, {{ Vite::asset('resources/images/podcast-coffee-logo-512.webp') }} 512w"
                                    sizes="(min-width: 768px) 280px, 160px"
                                />
                                <img
                                    src="{{ Vite::asset('resources/images/podcast-coffee-logo-512.webp') }}"
                                    alt=""
                                    width="512"
                                    height="512"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </picture>
                        @endif
                        <div class="[&_h3]:text-xl [&_h3]:leading-tight [&_h3]:text-gray-900 dark:[&_h3]:text-white lg:[&_h3]:text-2xl [&_p]:mt-4 [&_p]:leading-relaxed [&_p]:text-gray-600 dark:[&_p]:text-gray-300 p-5 sm:p-8">
                            <h3>Coffee With<br />The Laravel Architect</h3>
                            <p>
                                Conversations about Laravel, web development, and the developer life. One cup at a time.
                            </p>
                            <span class="text-brand-700 dark:text-brand-200 mt-6 block text-sm font-semibold group-hover:underline group-hover:underline-offset-4">Listen to the podcast <span aria-hidden="true">→</span></span>
                        </div>
                    </a>

                    @if ($latestYouTubeVideos->isNotEmpty())
                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach ($latestYouTubeVideos->take(2) as $video)
                                <x-home.youtube-thumbnail-card :video="$video" />
                            @endforeach
                        </div>
                    @elseif ($youtubeProfileUrl)
                        <a
                            href="{{ $youtubeProfileUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group focus-visible:outline-brand-500 dark:border-brand-700 [&_svg]:mb-5 [&_svg]:text-brand-700 dark:[&_svg]:text-brand-200 [&_h3]:text-xl [&_h3]:leading-tight [&_h3]:text-gray-900 dark:[&_h3]:text-white lg:[&_h3]:text-2xl [&_p]:mt-4 [&_p]:leading-relaxed [&_p]:text-gray-600 dark:[&_p]:text-gray-300 block border-t border-gray-200 py-6 focus-visible:outline-2 focus-visible:outline-offset-4 md:border-0 md:p-6"
                        >
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="3" />
                                <path d="m10 9 5 3-5 3Z" />
                            </svg>
                            <h3>Prefer to watch?</h3>
                            <p>Find The Laravel Architect on YouTube.</p>
                            <span
                                class="text-brand-700 dark:text-brand-200 mt-6 block text-sm font-semibold group-hover:underline group-hover:underline-offset-4"
                                >Visit the channel <span aria-hidden="true">↗</span
                                ><span class="sr-only"> (opens in a new tab)</span></span>
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- ===== FINAL CTA ===== --}}
        <section class="dark:bg-surface-control border-t border-gray-200 bg-gray-50 dark:border-white/5">
            <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6 md:py-28 lg:px-8">
                <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-green-300 bg-green-50 px-4 py-1.5 text-xs font-semibold tracking-widest text-green-800 uppercase dark:border-green-500/20 dark:bg-green-500/5 dark:text-green-400">
                    <span class="h-2 w-2 rounded-full bg-green-500 dark:bg-green-400" aria-hidden="true"></span>
                    Available for Projects
                </div>

                <h2 class="mb-6 text-4xl leading-tight font-extrabold text-gray-900 sm:text-5xl dark:text-white">
                    Let's build something maintainable.
                </h2>

                <p class="mx-auto mb-10 max-w-xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
                    Tell me what you're building, what needs to change, and where you're getting stuck.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <x-button href="{{ route('contact.create') }}" size="lg" class="group font-semibold">
                        Discuss a Project
                        <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </x-button>
                    <a
                        href="{{ route('projects.index') }}"
                        class="hover:border-brand-500 hover:text-brand-700 dark:border-brand-800 dark:hover:border-brand-500 inline-flex items-center gap-2 rounded-xl border border-gray-300 px-8 py-4 text-lg font-semibold text-gray-700 transition-colors dark:text-gray-300 dark:hover:text-white"
                    >
                        View Projects
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.site>
