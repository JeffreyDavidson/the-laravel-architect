@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'href' => null,
    'linkLabel' => null,
])

<div {{ $attributes->class(['mb-10 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)
            <p class="text-brand-600 dark:text-brand-300 mb-3 font-mono text-sm font-medium tracking-wide uppercase">
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="text-3xl font-semibold tracking-tight text-balance text-gray-900 sm:text-4xl dark:text-white">
            {{ $title }}
        </h2>

        @if ($description)
            <p class="mt-4 max-w-[62ch] text-base text-pretty text-gray-600 sm:text-lg dark:text-gray-400">
                {{ $description }}
            </p>
        @endif
    </div>

    @if ($href && $linkLabel)
        <a
            href="{{ $href }}"
            class="text-brand-700 hover:bg-brand-100 hover:text-brand-800 focus-visible:outline-brand-500 dark:text-brand-300 dark:hover:bg-brand-800/50 dark:hover:text-brand-200 inline-flex shrink-0 items-center gap-2 self-start rounded-lg px-3 py-2 text-base font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 sm:self-auto sm:text-sm"
        >
            {{ $linkLabel }}
            <span aria-hidden="true">&rarr;</span>
        </a>
    @endif
</div>
