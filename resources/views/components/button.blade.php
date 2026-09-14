@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button', 'class' => ''])

@php
    $base = 'inline-flex items-center gap-2 font-bold transition-[color,border-color,background-color,box-shadow] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400';
    $variants = [
        'primary' => 'bg-brand-action hover:bg-brand-action-hover text-white rounded-xl',
        'outline' => 'border border-gray-200 dark:border-surface-border hover:border-gray-400 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl bg-white dark:bg-surface-control',
    ];
    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-lg',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']).' '.$class;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
