@props([
    'title',
    'description',
    'variant' => 'brand',
])

@php
    $accent = $variant === 'accent';
@endphp

<div {{ $attributes->class([
    'about-value-card rounded-2xl border border-gray-200 bg-white p-6 dark:border-[#1e2a3a] dark:bg-[#0D1117]',
    'about-value-card-accent' => $accent,
]) }}>
    <x-public.section-icon :variant="$accent ? 'accent' : 'brand'" class="mb-4">
        {{ $icon }}
    </x-public.section-icon>
    <h3 class="mb-2 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $description }}</p>
</div>
