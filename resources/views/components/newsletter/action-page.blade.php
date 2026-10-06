@props([
    'command',
    'title',
    'description',
    'actionUrl',
    'buttonLabel',
    'method' => 'POST',
    'pendingLabel' => null,
    'seoSource' => null,
    'structuredData' => [],
])

{{-- A pending label makes the form submit itself once the page starts (see resources/js/pages/newsletter-confirm.js). --}}
<x-layouts.site :seo-source="$seoSource" :structured-data="$structuredData">
    <x-page-section>
        <div class="mx-auto max-w-xl text-center">
            <x-terminal-prompt :command="$command" />
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $title }}</h1>
            <p class="mt-4 text-gray-600 dark:text-gray-400">{{ $description }}</p>

            <form
                action="{{ $actionUrl }}"
                method="POST"
                class="mt-8"
                @if ($pendingLabel)
                    data-newsletter-confirm
                    x-data="newsletterConfirm"
                @endif
            >
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif
                @if ($pendingLabel)
                    <p
                        role="status"
                        class="mb-4 text-sm text-gray-600 dark:text-gray-400"
                        hidden
                        x-bind:hidden="notConfirming"
                    >
                        {{ $pendingLabel }}
                    </p>
                    <x-button type="submit" x-bind:disabled="confirming">{{ $buttonLabel }}</x-button>
                @else
                    <x-button type="submit">{{ $buttonLabel }}</x-button>
                @endif
            </form>
        </div>
    </x-page-section>
</x-layouts.site>
