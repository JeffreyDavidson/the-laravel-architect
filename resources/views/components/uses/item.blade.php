@props(['item'])

@php
    $classes = 'group flex items-start gap-4 border-t border-gray-200 py-5 transition-colors duration-200 hover:border-brand-600/40 dark:border-surface-border dark:hover:border-brand-500/50';
@endphp

@if (isset($item['url']))
    <a
        href="{{ $item['url'] }}"
        target="_blank"
        rel="noopener noreferrer"
        @if (($item['name'] ?? null) === 'GitHub') data-fathom-event="github profile click" @endif
        class="{{ $classes }} underline decoration-transparent underline-offset-4 hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400"
    >
        <x-uses.item-content :item="$item" linked />
    </a>
@else
    <div class="{{ $classes }}">
        <x-uses.item-content :item="$item" />
    </div>
@endif
