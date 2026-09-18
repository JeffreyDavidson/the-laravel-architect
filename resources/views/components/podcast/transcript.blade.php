@props(['content'])

<section class="mb-12" aria-labelledby="episode-transcript-heading">
    <h2 id="episode-transcript-heading" class="sr-only">Transcript</h2>
    <details
        data-transcript
        class="dark:border-surface-border dark:bg-surface-control overflow-hidden rounded-2xl border border-gray-200 bg-white"
    >
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 text-lg font-extrabold text-gray-900 marker:hidden dark:text-white">
            <span class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[color-mix(in_srgb,var(--podcast-color)_6%,transparent)]">
                    <svg class="text-archive-link h-4 w-4 dark:text-[var(--podcast-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m8-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                Transcript
            </span>
            <span class="text-sm font-semibold text-gray-500">Read transcript</span>
        </summary>
        <div class="dark:border-surface-border border-t border-gray-200 px-6 py-6 md:px-8">
            <div
                data-transcript-tools
                hidden
                class="dark:border-surface-border dark:bg-surface-control/50 mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label for="episode-transcript-search" class="sr-only">Search transcript</label>
                    <input
                        id="episode-transcript-search"
                        type="search"
                        data-transcript-search
                        placeholder="Search transcript"
                        autocomplete="off"
                        class="dark:border-surface-border dark:bg-surface-control focus:border-brand-600 focus:ring-brand-600/10 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:ring-2 dark:text-gray-100"
                    />
                    <p data-transcript-status aria-live="polite" class="text-sm text-gray-500 dark:text-gray-400">
                        Search the transcript
                    </p>
                </div>
            </div>
            <x-markdown
                :content="$content"
                data-transcript-content
                class="prose-invert prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-headings:font-extrabold prose-a:no-underline hover:prose-a:underline prose-code:font-mono prose-pre:bg-gray-50 dark:prose-pre:bg-surface-control prose-pre:border prose-pre:border-gray-200 dark:prose-li:text-gray-600 dark:prose-p:text-gray-600 dark:prose-p:text-gray-400 [--tw-prose-code:var(--accent-pink)] [--tw-prose-links:var(--podcast-color)]"
            />
        </div>
    </details>
</section>
