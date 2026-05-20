@props(['variant' => 'brand'])

@php
    $variantClasses = [
        'brand' => 'bg-brand-600/10 text-brand-600',
        'accent' => 'bg-accent-600/10 text-accent-600',
    ][$variant] ?? 'bg-brand-600/10 text-brand-600';
@endphp

<div {{ $attributes->class(['flex h-10 w-10 items-center justify-center rounded-xl', $variantClasses]) }}>
    {{ $slot }}
</div>
