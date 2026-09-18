@props(['active' => false, 'count'])

<a
    @if ($active) aria-current="page" @endif
    {{ $attributes->class('relative cursor-pointer whitespace-nowrap py-2 text-sm font-semibold text-archive-muted transition-colors duration-200 hover:text-archive-link aria-[current=page]:text-archive-link after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:scale-x-60 after:bg-brand-600 after:opacity-0 after:transition-[opacity,scale] after:duration-200 aria-[current=page]:after:scale-x-100 aria-[current=page]:after:opacity-100 motion-reduce:transition-none motion-reduce:after:transition-none') }}
>
    {{ $slot }} <span class="text-archive-count ml-1 tabular-nums">{{ $count }}</span>
</a>
