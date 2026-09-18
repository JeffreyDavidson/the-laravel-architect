<x-layouts.site
    :seo-source="$seoSource ?? null"
    :post="$post ?? null"
    :podcast="$podcast ?? null"
    :episode="$episode ?? null"
    :project="$project ?? null"
    :posts="$posts ?? null"
    :selected-category="$selectedCategory ?? null"
    :category="$category ?? null"
    :tag="$tag ?? null"
    :projects="$projects ?? null"
    :episodes="$episodes ?? null"
    :items="$items ?? null"
>
    <x-slot:head>
        <link
            rel="alternate"
            type="application/rss+xml"
            title="The Laravel Architect Newsletter"
            href="{{ route('newsletter.rss') }}"
        />
    </x-slot:head>

    <div class="dark:bg-surface-page bg-gray-50">
        <header class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
                <p class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase">
                    Newsletter
                </p>
                <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl dark:text-white">
                    Notes worth keeping.
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-pretty text-gray-600 dark:text-gray-400">
                    Practical Laravel architecture notes, tutorials, and updates from the work behind The Laravel
                    Architect.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <section aria-labelledby="newsletter-issues-heading">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2
                            id="newsletter-issues-heading"
                            class="text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl dark:text-white"
                        >
                            Archive
                        </h2>
                        <p class="mt-2 text-base text-gray-600 dark:text-gray-400">
                            Browse past issues at your own pace.
                        </p>
                    </div>
                    <a
                        href="#newsletter-signup"
                        class="text-brand-600 dark:text-brand-300 text-sm font-semibold hover:underline"
                    >Subscribe by email</a>
                    <a
                        href="{{ route('newsletter.rss') }}"
                        class="text-brand-600 dark:text-brand-300 text-sm font-semibold hover:underline"
                    >Subscribe via RSS</a>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    @forelse ($issues as $issue)
                        <article class="dark:border-surface-border dark:bg-brand-900/60 rounded-xl border border-gray-200 bg-white p-6 outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10">
                            <time
                                class="text-brand-600 dark:text-brand-300 font-mono text-xs font-semibold tracking-wide uppercase"
                                datetime="{{ $issue->published_at?->toDateString() }}"
                            >
                                {{ $issue->published_at?->format('F j, Y') }}
                            </time>
                            <h3 class="mt-3 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                <a
                                    href="{{ route('newsletter.issue', $issue) }}"
                                    class="hover:text-brand-600 dark:hover:text-brand-300 focus-visible:outline-brand-500 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4"
                                >
                                    {{ $issue->title }}
                                </a>
                            </h3>
                            @if ($issue->excerpt)
                                <p class="mt-3 text-base leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ $issue->excerpt }}
                                </p>
                            @endif
                            <a
                                href="{{ route('newsletter.issue', $issue) }}"
                                class="text-brand-600 dark:text-brand-300 mt-5 inline-flex text-sm font-semibold hover:underline"
                            >
                                Read issue <span aria-hidden="true" class="ml-1">→</span>
                            </a>
                        </article>
                    @empty
                        <div class="dark:border-surface-border col-span-full rounded-xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-950 dark:text-white">No issues yet</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                New newsletter issues will appear here after they are published.
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($issues->hasPages())
                    <div class="mt-10">{{ $issues->links() }}</div>
                @endif
            </section>

            <section id="newsletter-signup" class="mt-16">
                <x-home.newsletter-signup class="mx-auto max-w-3xl text-center" />
            </section>
        </main>
    </div>
    </x-layouts.app>
