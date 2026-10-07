@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])

{{--
    primary and outline are the standard buttons and take a size. brand (darker on hover, with a focus ring) and
    brand-soft (lighter on hover) are the flat brand-600 buttons on the projects, archive, search and error pages;
    they ignore size, so callers add their own padding and layout classes.
--}}
@php
    $base = 'inline-flex items-center gap-2 font-bold transition-[color,border-color,background-color,box-shadow] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400';
    $sizeClasses = match ($size) {
        'sm' => 'px-4 py-2 text-sm',
        'lg' => 'px-8 py-4 text-lg',
        default => 'px-6 py-3 text-sm',
    };
    $classes = match ($variant) {
        'outline' => [$base, 'border border-gray-200 dark:border-surface-border hover:border-gray-400 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl bg-white dark:bg-surface-control', $sizeClasses],
        'brand' => ['focus-visible:outline-brand-500 bg-brand-600 hover:bg-brand-700 rounded-lg px-5 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4'],
        'brand-soft' => ['bg-brand-600 hover:bg-brand-500 rounded-lg font-semibold text-white'],
        default => [$base, 'bg-brand-action hover:bg-brand-action-hover text-white rounded-xl', $sizeClasses],
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
