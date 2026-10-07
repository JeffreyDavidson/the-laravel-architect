@props([
    'heading',
    'href',
    'buttonLabel',
    'variant' => 'band',
    'headingId' => null,
    'badge' => 'Available for Projects',
])

{{--
    The closing call to action that sends visitors to the contact form; the default slot is the supporting sentence.
    band: a full-width strip with the heading and sentence beside the button, labelled by heading-id. Pass the
    border (border-t or border-y) as a class.
    availability: the centred block with the availability badge and a large button. The optional decoration slot
    renders behind the content.
--}}
@if ($variant === 'availability')
    <div {{ $attributes->class('dark:border-brand-700 dark:bg-surface-page relative overflow-hidden border-t border-gray-200 bg-gray-50') }}>
        {{ $decoration ?? '' }}

        <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 md:py-28 lg:px-8">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-green-500/20 bg-green-500/10 px-4 py-1.5 text-xs font-bold tracking-widest text-green-800 uppercase dark:text-green-400">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                </span>
                {{ $badge }}
            </div>
            <h2 class="mb-4 text-3xl font-extrabold md:text-4xl">
                <span>{{ $heading }}</span>
            </h2>
            <p class="mx-auto mb-8 max-w-xl text-lg text-gray-600 dark:text-gray-400">{{ $slot }}</p>
            <x-button :href="$href" class="px-8 py-3.5 text-lg">
                {{ $buttonLabel }}
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </x-button>
        </div>
    </div>
@else
    <section
        aria-labelledby="{{ $headingId }}"
        {{ $attributes->class('dark:border-brand-800 dark:bg-brand-900/30 border-gray-200 bg-gray-50 py-10 sm:py-14') }}
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <h2
                    id="{{ $headingId }}"
                    class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white"
                >
                    {{ $heading }}
                </h2>
                <p class="mt-3 text-base leading-7 text-gray-600 dark:text-gray-400">{{ $slot }}</p>
            </div>
            <x-button :href="$href" variant="brand" class="w-fit shrink-0 py-3">{{ $buttonLabel }}</x-button>
        </div>
    </section>
@endif
