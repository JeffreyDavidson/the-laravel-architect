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
            'card' => '[--card-color:var(--color-brand-600)]',
            'text' => 'text-brand-600 dark:text-brand-400',
            'border' => 'border-brand-500/20',
            'tag' => 'text-brand-600 dark:text-brand-300 bg-brand-500/10 border-brand-500/20',
        ],
        'accent' => [
            'card' => '[--card-color:var(--accent-pink)]',
            'text' => 'text-accent-700 dark:text-accent-400',
            'border' => 'border-accent-500/20',
            'tag' => 'text-accent-700 dark:text-accent-300 bg-accent-500/10 border-accent-500/20',
        ],
        'green' => [
            'card' => '[--card-color:var(--accent-green)]',
            'text' => 'text-green-700 dark:text-green-400',
            'border' => 'border-green-500/20',
            'tag' => 'text-green-700 dark:text-green-300 bg-green-500/10 border-green-500/20',
        ],
        'amber' => [
            'card' => '[--card-color:var(--accent-amber)]',
            'text' => 'text-amber-700 dark:text-amber-400',
            'border' => 'border-amber-500/20',
            'tag' => 'text-amber-700 dark:text-amber-300 bg-amber-500/10 border-amber-500/20',
        ],
    ][$variant];
@endphp

<div {{ $attributes->class(['group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 border-t-[3px] border-t-[var(--card-color)] bg-white p-10 transition-[background-color,border-color] duration-200 hover:border-[var(--card-color)] hover:bg-[var(--surface-service-hover)] dark:border-white/10 dark:border-t-[var(--card-color)] dark:bg-surface-card', $colors['card'], 'fade-up']) }}>
    <span class="pointer-events-none absolute -top-2 right-4 font-mono text-8xl leading-none font-black text-black opacity-[0.04] transition-opacity duration-200 group-hover:opacity-[0.08] dark:text-white">{{ $number }}</span>

    <div class="relative z-10 mb-5 inline-block rounded-lg border {{ $colors['border'] }} bg-inset px-4 py-2.5 font-mono text-sm text-[var(--text-primary)] dark:bg-surface-control dark:text-[var(--text-code)]">
        <span class="text-[var(--text-tertiary)]">$</span>
        <span class="{{ $colors['text'] }}">php artisan</span>
        <span class="text-gray-900 dark:text-white">{{ $command }}</span
        ><span class="relative -top-px {{ $colors['text'] }}">▊</span>
    </div>

    <h3 class="relative z-10 mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="relative z-10 mb-5 flex-grow text-sm leading-relaxed text-gray-600 dark:text-gray-400">
        {{ $description }}
    </p>

    <div class="relative z-10 flex flex-wrap gap-2">
        @foreach ($tags as $tag)
            <span class="rounded-full border px-2.5 py-1 text-xs font-semibold uppercase tracking-wider {{ $colors['tag'] }}">{{ $tag }}</span>
        @endforeach
    </div>

    <a
        href="{{ $href }}"
        class="relative z-10 mt-5 inline-flex items-center gap-2 text-[0.8125rem] font-semibold transition-colors {{ $colors['text'] }}"
    >
        {{ $cta }}
        <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
    </a>
</div>
