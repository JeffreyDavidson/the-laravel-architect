@props(['mobile' => false])

<button
    @if (! $mobile) id="theme-toggle" @endif
    data-theme-toggle
    @class([
        'theme-toggle-mobile flex items-center gap-2 px-2 py-1 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $mobile,
        'dark:hover:bg-brand-800/50 rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => ! $mobile,
    ])
    title="Toggle theme"
    aria-label="Toggle theme"
    aria-pressed="false"
>
    <svg
        @if (! $mobile) id="theme-icon-dark" @endif
        class="{{ $mobile ? 'h-4 w-4' : 'hidden h-5 w-5' }}"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
    ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9.003 9.003 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
    @if (! $mobile)
        <svg id="theme-icon-light" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
    @else
        <span class="theme-toggle-label">Light Mode</span>
    @endif
</button>
