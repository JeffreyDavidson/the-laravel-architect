@props(['content'])

<div class="mb-12">
    <h2 class="mb-6 flex items-center gap-3 text-2xl font-extrabold">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[color-mix(in_srgb,var(--podcast-color)_6%,transparent)]">
            <svg class="text-archive-link h-4 w-4 dark:text-[var(--podcast-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </span>
        Show Notes
    </h2>
    <x-markdown
        :content="$content"
        class="prose-invert prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-headings:font-extrabold prose-a:no-underline hover:prose-a:underline prose-code:font-mono prose-pre:bg-gray-50 dark:prose-pre:bg-surface-control prose-pre:border prose-pre:border-gray-200 dark:prose-li:text-gray-600 dark:prose-p:text-gray-600 dark:prose-p:text-gray-400 [--tw-prose-code:var(--accent-pink)] [--tw-prose-links:var(--podcast-color)]"
    />
</div>
