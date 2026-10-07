@props(['variant' => 'label', 'as' => 'p'])

{{--
    The small uppercase label above a heading.
    label: the mono label on page headers (label-large is its text-sm size).
    section: the bolder mono label on the archive and newsletter headers.
    heading: the label above home page section headings.
    category: the sans category label on home page post cards.
--}}
@php
    $variantClasses = match ($variant) {
        'label' => 'text-brand-600 tracking-label font-mono text-xs uppercase',
        'label-large' => 'text-brand-600 tracking-label font-mono text-sm uppercase',
        'section' => 'text-brand-600 dark:text-brand-300 font-mono text-sm font-semibold tracking-wide uppercase',
        'heading' => 'text-brand-600 dark:text-brand-300 font-mono text-sm font-medium tracking-wide uppercase',
        'category' => 'text-brand-400 text-xs font-semibold tracking-wide uppercase',
    };
@endphp

<{{ $as }} {{ $attributes->class($variantClasses) }}>{{ $slot }}</{{ $as }}>
