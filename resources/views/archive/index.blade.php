@extends('layouts.app')

@section('title', 'Archive')

@section('content')
    <div class="dark:bg-surface-page bg-gray-50">
        <header class="dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
                <p class="text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase">
                    Archive
                </p>
                <h1 class="mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl dark:text-white">
                    Everything worth revisiting.
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-pretty text-gray-600 dark:text-gray-400">
                    Browse the complete public collection of writing, projects, conversations, newsletters, and videos.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <form
                method="GET"
                action="{{ route('archive.index') }}"
                aria-label="Filter archive"
                class="dark:border-surface-border dark:bg-surface-control grid gap-4 rounded-2xl border border-gray-200 bg-white p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end"
            >
                <div>
                    <label for="archive-type" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >Collection</label>
                    <x-form.select
                        id="archive-type"
                        name="type"
                        :options="$typeOptions"
                        :value="$selectedType"
                        placeholder="All content"
                    />
                </div>
                <div>
                    <label for="archive-year" class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white"
                        >Year</label>
                    <x-form.select
                        id="archive-year"
                        name="year"
                        :options="$yearOptions"
                        :value="$selectedYear"
                        placeholder="Any year"
                    />
                </div>
                <button
                    type="submit"
                    class="bg-brand-600 hover:bg-brand-500 focus-visible:outline-brand-500 inline-flex min-h-11 items-center justify-center rounded-lg px-5 py-2.5 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2"
                >
                    Apply filters
                </button>
            </form>

            <div class="mt-10 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2
                        id="archive-results-heading"
                        class="text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl dark:text-white"
                    >
                        Public archive
                    </h2>
                    <p class="mt-2 text-base text-gray-600 dark:text-gray-400" aria-live="polite" role="status">
                        @if ($items->total() > 0)
                            Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} items.
                        @else
                            No items match these filters.
                        @endif
                    </p>
                </div>
                <a
                    href="{{ route('search') }}"
                    class="text-brand-600 dark:text-brand-300 text-sm font-semibold hover:underline"
                >Search the archive →</a>
            </div>

            <section aria-labelledby="archive-results-heading" class="mt-8">
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($items as $item)
                        <article class="dark:border-surface-border dark:bg-brand-900/60 flex h-full flex-col rounded-xl border border-gray-200 bg-white p-6 outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10">
                            <div class="flex items-center justify-between gap-3 text-xs font-semibold tracking-wide uppercase">
                                <span class="text-brand-600 dark:text-brand-300">{{ $item['typeLabel'] }}</span>
                                <time
                                    class="font-mono text-gray-500 dark:text-gray-400"
                                    datetime="{{ $item['dateTime'] }}"
                                >{{ $item['date'] }}</time>
                            </div>
                            <h3 class="mt-4 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                <a
                                    href="{{ $item['url'] }}"
                                    @if ($item['external']) target="_blank" rel="noopener noreferrer" @endif
                                    class="hover:text-brand-600 dark:hover:text-brand-300 focus-visible:outline-brand-500 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4"
                                >
                                    {{ $item['title'] }}
                                    @if ($item['external'])
                                        <span aria-hidden="true">↗</span>
                                    @endif
                                </a>
                            </h3>
                            @if ($item['summary'])
                                <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                    {{ $item['summary'] }}
                                </p>
                            @endif
                            <a
                                href="{{ $item['url'] }}"
                                @if ($item['external']) target="_blank" rel="noopener noreferrer" @endif
                                class="text-brand-600 dark:text-brand-300 mt-auto inline-flex pt-5 text-sm font-semibold hover:underline"
                            >
                                View {{ strtolower($item['typeLabel']) }} <span aria-hidden="true" class="ml-1">→</span>
                            </a>
                        </article>
                    @empty
                        <div class="dark:border-surface-border col-span-full rounded-xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-700">
                            <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Nothing here yet</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Try another collection or year.</p>
                            <a
                                href="{{ route('archive.index') }}"
                                class="text-brand-600 dark:text-brand-300 mt-5 inline-flex text-sm font-semibold hover:underline"
                            >Show the full archive</a>
                        </div>
                    @endforelse
                </div>

                @if ($items->hasPages())
                    <div class="mt-10">{{ $items->links() }}</div>
                @endif
            </section>
        </main>
    </div>
@endsection
