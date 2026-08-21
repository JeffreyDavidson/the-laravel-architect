@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'href' => null,
    'linkLabel' => null,
])

<div {{ $attributes->class(['mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-2xl">
        @if($eyebrow)
            <p class="mb-3 font-mono text-sm font-medium uppercase tracking-wide text-brand-600 dark:text-brand-300">
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="text-balance text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
            {{ $title }}
        </h2>

        @if($description)
            <p class="mt-4 max-w-[62ch] text-pretty text-base text-gray-600 dark:text-gray-400 sm:text-lg">
                {{ $description }}
            </p>
        @endif
    </div>

    @if($href && $linkLabel)
        <a href="{{ $href }}" class="inline-flex shrink-0 items-center gap-2 self-start rounded-lg px-3 py-2 text-base font-medium text-brand-700 transition-colors hover:bg-brand-100 hover:text-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:text-brand-300 dark:hover:bg-brand-800/50 dark:hover:text-brand-200 sm:self-auto sm:text-sm">
            {{ $linkLabel }}
            <span aria-hidden="true">&rarr;</span>
        </a>
    @endif
</div>
