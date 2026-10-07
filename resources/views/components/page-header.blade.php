@use('Illuminate\View\ComponentAttributeBag')
@use('Illuminate\View\ComponentSlot')

@props([
    'eyebrow' => null,
    'eyebrowVariant' => 'label',
    'title' => null,
    'description' => null,
    'backHref' => null,
    'backLabel' => null,
    'width' => '7xl',
    'compact' => false,
])

{{--
    The bordered white header at the top of a listing page: an optional back link, eyebrow, title and description,
    then the default slot (a search form, for example).

    The eyebrow, title and description are plain strings or named slots with their own attributes. A title or
    description without a class gets the standard page header styles. Full-width (7xl) headers grow their padding
    from the md breakpoint and narrower ones from sm; compact headers use less vertical padding.
--}}
@php
    $maxWidth = match ($width) {
        '7xl' => 'max-w-7xl',
        '6xl' => 'max-w-6xl',
        '4xl' => 'max-w-4xl',
    };

    $padding = match (true) {
        $width === '7xl' && $compact => 'py-12 md:py-16',
        $width === '7xl' => 'py-14 md:py-20',
        $compact => 'py-12 sm:py-16',
        default => 'py-14 sm:py-20',
    };

    $slotAttributes = fn (mixed $part, string $defaultClasses): ComponentAttributeBag => match (true) {
        ! $part instanceof ComponentSlot => (new ComponentAttributeBag)->class($defaultClasses),
        $part->attributes->has('class') => $part->attributes,
        default => $part->attributes->class($defaultClasses),
    };
@endphp

<header {{ $attributes->class('dark:border-surface-border dark:bg-surface-page border-b border-gray-200 bg-white') }}>
    <div @class(['mx-auto px-4 sm:px-6 lg:px-8', $maxWidth, $padding])>
        @if ($backHref)
            <a
                href="{{ $backHref }}"
                class="text-brand-600 hover:text-brand-500 mb-4 inline-flex items-center gap-1 text-sm transition-colors"
            >
                <x-svg-icon name="chevron-left" class="h-4 w-4" />
                {{ $backLabel }}
            </a>
        @endif

        @if (filled($eyebrow))
            <x-eyebrow :variant="$eyebrowVariant" :attributes="$slotAttributes($eyebrow, '')">{{ $eyebrow }}</x-eyebrow>
        @endif

        @if (filled($title))
            <h1 {{ $slotAttributes($title, 'mt-4 max-w-3xl text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl dark:text-white') }}>
                {{ $title }}
            </h1>
        @endif

        @if (filled($description))
            <p {{ $slotAttributes($description, 'mt-5 max-w-2xl text-lg text-pretty text-gray-600 dark:text-gray-400') }}>
                {{ $description }}
            </p>
        @endif

        {{ $slot }}
    </div>
</header>
