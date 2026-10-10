@props(['size' => 'md'])

@php
    $small = $size === 'sm';
@endphp

<img
    src="/images/elephant-companion-128.webp"
    alt=""
    width="{{ $small ? 40 : 52 }}"
    height="{{ $small ? 40 : 52 }}"
    @if ($small) loading="lazy" @endif
    decoding="async"
    @class([
        'shrink-0 object-contain',
        'size-10' => $small,
        'size-13' => ! $small,
    ])
/>
<span class="flex flex-col gap-0.5 leading-none">
    <span class="text-brand-600 dark:text-brand-300 text-meta tracking-micro font-mono font-medium uppercase transition-colors">The Laravel</span>
    <span
        @class([
            'font-empera group-hover:text-brand-action dark:group-hover:text-brand-200 leading-none tracking-[0.04em] text-gray-950 transition-colors dark:text-white',
            'text-xl' => $small,
            'text-2xl' => ! $small,
        ])
    >Architect</span>
</span>
