<x-layouts.site :page-meta="$pageMeta">
    {{-- Hero --}}
    <x-hero-section>
        <div class="grid gap-6 md:grid-cols-[8rem_1fr] md:gap-10">
            <p class="text-brand-600 tracking-label font-mono text-xs uppercase">Toolkit / 05</p>
            <div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 md:text-6xl dark:text-white">
                    The tools behind the work.
                </h1>
                <p class="text-lg leading-relaxed text-gray-600 md:text-xl dark:text-gray-400">
                    The hardware, software, and tools I use daily for development, content creation, and life. Inspired
                    by
                    <a
                        href="https://uses.tech"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-brand-600 decoration-brand-600/50 focus-visible:outline-brand-400 underline underline-offset-4 hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2"
                        >uses.tech</a
                    >.
                </p>
                <p class="mt-5 font-mono text-xs tracking-wide text-gray-500 uppercase">Last updated February 2026</p>
            </div>
        </div>
    </x-hero-section>

    <nav
        aria-label="Jump to uses section"
        class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white lg:hidden"
    >
        <div class="mx-auto flex max-w-7xl gap-6 overflow-x-auto px-4 py-4 font-mono text-xs tracking-wide text-gray-600 uppercase sm:px-6 dark:text-gray-400">
            @foreach ($sections as $section)
                <a
                    href="#{{ $section['id'] }}"
                    class="hover:text-brand-action dark:hover:text-brand-600 whitespace-nowrap"
                >{{ $section['shortLabel'] }}</a>
            @endforeach
        </div>
    </nav>

    {{-- Content --}}
    <div class="dark:bg-surface-page bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 md:py-12 lg:px-8">
            <div class="flex flex-col gap-12 lg:flex-row">
                {{-- Main Content --}}
                <div class="min-w-0 flex-1">
                    @foreach ($sections as $section)
                        <section id="{{ $section['id'] }}" @class(['mb-16' => ! $loop->last, 'scroll-mt-24'])>
                            <div class="mb-8 flex items-center gap-3">
                                <x-public.section-icon :variant="$section['iconVariant']">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}" /></svg>
                                </x-public.section-icon>
                                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                    {{ $section['heading'] }}
                                </h2>
                            </div>
                            @if ($section['siteTechnology'])
                                <div class="dark:border-surface-border grid grid-cols-2 border-y border-gray-200 sm:grid-cols-3">
                                    @foreach ($section['items'] as $tech)
                                        <x-uses.site-tech :tech="$tech" />
                                    @endforeach
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($section['items'] as $item)
                                        <x-uses.item :item="$item" />
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>

                {{-- Sidebar --}}
                <div class="hidden flex-shrink-0 lg:block lg:w-72">
                    <div class="space-y-6 lg:sticky lg:top-24">
                        {{-- Quick nav --}}
                        <section class="dark:border-surface-border border-t border-gray-200 pt-5">
                            <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">Jump To</h3>
                            <nav aria-label="Jump to uses section" class="space-y-2">
                                @foreach ($sections as $section)
                                    <a
                                        href="#{{ $section['id'] }}"
                                        class="hover:text-brand-action block text-sm text-gray-600 transition-colors dark:text-gray-400"
                                    >{{ $section['label'] }}</a>
                                @endforeach
                            </nav>
                        </section>

                        {{-- uses.tech --}}
                        <section class="dark:border-surface-border border-t border-gray-200 pt-5">
                            <h3 class="mb-3 text-xs font-semibold tracking-widest text-gray-500 uppercase">
                                Inspired By
                            </h3>
                            <a
                                href="https://uses.tech"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-brand-600 focus-visible:outline-brand-400 inline-flex items-center gap-1.5 text-sm hover:underline focus-visible:outline-2 focus-visible:outline-offset-2"
                            >
                                uses.tech
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                            <p class="mt-1.5 text-xs text-gray-500">A directory of developer /uses pages.</p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.site>
