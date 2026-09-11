@props(['item'])

<div {{ $attributes->class('about-timeline-item') }}>
    <span class="text-brand-600 text-xs font-bold">{{ $item['year'] }}</span>
    <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $item['title'] }}</p>
    <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
</div>
