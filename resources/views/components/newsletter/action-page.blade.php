@props([
    'command',
    'title',
    'description',
    'actionUrl',
    'buttonLabel',
    'method' => 'POST',
    'pendingLabel' => null,
    'pageMeta',
])

{{--
    A pending label renders the page in its pending state from the first paint and submits the form once it starts
    (see resources/js/pages/newsletter-confirm.js). The button sits in a noscript block for visitors without
    JavaScript; with it, the component reveals a second copy only if submitting fails or stalls.
--}}
<x-layouts.site :page-meta="$pageMeta">
    <x-page-section>
        <div class="mx-auto max-w-xl text-center">
            <x-terminal-prompt :command="$command" />
            @if ($pendingLabel)
                <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    {{ $pendingLabel }}
                </h1>

                <form
                    action="{{ $actionUrl }}"
                    method="POST"
                    class="mt-4"
                    data-newsletter-confirm
                    x-data="newsletterConfirm"
                >
                    @csrf
                    <p
                        role="status"
                        class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400"
                        data-newsletter-confirm-status
                        x-bind:hidden="fallbackShown"
                    >
                        <svg class="text-brand-600 h-4 w-4 motion-safe:animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        One moment.
                    </p>
                    <noscript>
                        <style>
                            [data-newsletter-confirm-status] {
                                display: none;
                            }
                        </style>
                        <p class="text-gray-600 dark:text-gray-400">{{ $description }}</p>
                        <x-button type="submit" class="mt-8">{{ $buttonLabel }}</x-button>
                    </noscript>
                    <div hidden x-bind:hidden="fallbackHidden">
                        <p class="text-gray-600 dark:text-gray-400">{{ $description }}</p>
                        <x-button type="submit" class="mt-8">{{ $buttonLabel }}</x-button>
                    </div>
                </form>
            @else
                <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $title }}</h1>
                <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $description }}</p>

                <form action="{{ $actionUrl }}" method="POST" class="mt-8">
                    @csrf
                    @if ($method !== 'POST')
                        @method($method)
                    @endif
                    <x-button type="submit">{{ $buttonLabel }}</x-button>
                </form>
            @endif
        </div>
    </x-page-section>
</x-layouts.site>
