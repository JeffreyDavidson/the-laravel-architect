@props(['episode'])

@if ($episode->guest_name)
    <div class="dark:border-surface-border dark:bg-surface-control mb-10 rounded-2xl border border-gray-200 bg-white p-6">
        <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">Featured Guest</h3>
        <div class="flex items-start gap-4">
            <div class="text-archive-link flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-[var(--archive-link-alpha-08)] text-xl font-bold dark:bg-[color-mix(in_srgb,var(--podcast-color)_8%,transparent)] dark:text-[var(--podcast-color)]">
                {{ substr($episode->guest_name, 0, 1) }}
            </div>
            <div>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $episode->guest_name }}</p>
                @if ($episode->guest_title)
                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">{{ $episode->guest_title }}</p>
                @endif
                @if ($episode->guest_url)
                    <a
                        href="{{ $episode->guest_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-archive-link mt-2 inline-flex items-center gap-1.5 text-sm hover:underline dark:text-[var(--podcast-color)]"
                    >
                        {{ parse_url($episode->guest_url, PHP_URL_HOST) }}
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
