@props(['variant' => 'primary', 'href' => null, 'type' => 'button', 'class' => ''])

@php
$base = 'inline-flex items-center gap-2 font-bold transition-[color,border-color,background-color,box-shadow] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400';
$variants = [
    'primary' => 'bg-[#3f6fa8] hover:bg-[#345f91] text-white rounded-xl',
    'outline' => 'border border-gray-200 dark:border-[#1e2a3a] hover:border-gray-400 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl bg-white dark:bg-[#0D1117]',
];
$sizes = 'px-6 py-3 text-sm'; // default size, can be overridden via class prop
$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $sizes . ' ' . $class;
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
