@props([
    'variant' => 'brand',
    'number',
    'command',
    'title',
    'description',
    'tags' => [],
    'href',
    'cta',
])

@php
    $colors = [
        'brand' => [
            'card' => 'service-card-brand',
            'text' => 'text-brand-400',
            'border' => 'border-brand-500/20',
            'tag' => 'text-brand-600 dark:text-brand-300 bg-brand-500/10 border-brand-500/20',
            'dot' => 'bg-brand-400',
        ],
        'accent' => [
            'card' => 'service-card-accent',
            'text' => 'text-accent-400',
            'border' => 'border-accent-500/20',
            'tag' => 'text-accent-600 dark:text-accent-300 bg-accent-500/10 border-accent-500/20',
            'dot' => 'bg-accent-400',
        ],
        'green' => [
            'card' => 'service-card-green',
            'text' => 'text-green-400',
            'border' => 'border-green-500/20',
            'tag' => 'text-green-700 dark:text-green-300 bg-green-500/10 border-green-500/20',
            'dot' => 'bg-green-400',
        ],
        'amber' => [
            'card' => 'service-card-amber',
            'text' => 'text-amber-400',
            'border' => 'border-amber-500/20',
            'tag' => 'text-amber-700 dark:text-amber-300 bg-amber-500/10 border-amber-500/20',
            'dot' => 'bg-amber-400',
        ],
    ][$variant];

    $dotPositions = [
        'top-[20%] left-[80%] delay-0',
        'top-[60%] left-[90%] delay-500',
        'top-[40%] left-[15%] delay-1000',
        'top-[80%] left-[70%] delay-[1500ms]',
    ];
@endphp

<div {{ $attributes->class(['service-card-v2 fade-up', $colors['card']]) }}>
    <div class="service-orb"></div>

    <div class="service-dots">
        @foreach($dotPositions as $position)
            <span class="{{ $colors['dot'] }} {{ $position }}"></span>
        @endforeach
    </div>

    <span class="service-number {{ $colors['text'] }}">{{ $number }}</span>

    <div class="relative z-10 mb-5 inline-block rounded-lg border {{ $colors['border'] }} bg-[#0a0e14] px-4 py-2.5 font-mono text-sm">
        <span class="text-gray-500">$</span>
        <span class="{{ $colors['text'] }}">php artisan</span>
        <span class="text-white">{{ $command }}</span><span class="relative -top-px {{ $colors['text'] }}">▊</span>
    </div>

    <h3 class="relative z-10 mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="relative z-10 mb-5 flex-grow text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $description }}</p>

    <div class="relative z-10 flex flex-wrap gap-2">
        @foreach($tags as $tag)
            <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider {{ $colors['tag'] }}">{{ $tag }}</span>
        @endforeach
    </div>

    <a href="{{ $href }}" class="service-arrow relative z-10 {{ $colors['text'] }}">
        {{ $cta }}
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </a>
</div>
