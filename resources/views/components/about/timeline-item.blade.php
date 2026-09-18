@props(['item'])

<div {{ $attributes->class('relative pl-8 before:absolute before:left-0 before:top-2 before:size-2.5 before:rounded-full before:bg-brand-600 before:shadow-[0_0_10px_var(--brand-alpha-20)] dark:before:shadow-none after:absolute after:left-1 after:top-6 after:w-0.5 after:h-[calc(100%-0.5rem)] after:bg-[var(--shadow-black-10)] dark:after:bg-[var(--surface-timeline)] last:after:hidden') }}>
    <span class="text-brand-600 text-xs font-bold">{{ $item['year'] }}</span>
    <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $item['title'] }}</p>
    <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
</div>
