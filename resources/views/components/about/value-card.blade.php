@props([
    'title',
    'description',
    'variant' => 'brand',
])

@php
    $accent = $variant === 'accent';
@endphp

<div {{
    $attributes->class([
        'relative overflow-hidden border-t border-gray-200 bg-white py-6 transition-[border-color,box-shadow] duration-300 hover:border-brand-600/20 hover:shadow-sm dark:border-surface-border dark:bg-transparent dark:hover:border-brand-600/20',
    ])
}}>
    <x-public.section-icon :variant="$accent ? 'accent' : 'brand'" class="mb-4"> {{ $icon }} </x-public.section-icon>
    <h3 class="mb-2 font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $description }}</p>
</div>
