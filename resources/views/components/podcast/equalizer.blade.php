{{-- Animated equalizer bars in the podcast colour: the "badge" beside the show's episode count, or a "row" that fades in while its episode row is hovered. --}}
@props(['variant' => 'badge'])

@php($bars = $variant === 'row'
    ? ['width' => 'w-[2px]', 'durations' => ['[--dur:0.6s]', '[--dur:0.8s]', '[--dur:0.5s]', '[--dur:0.7s]']]
    : ['width' => 'w-[3px]', 'durations' => ['[--dur:0.7s]', '[--dur:0.5s]', '[--dur:0.8s]', '[--dur:0.6s]']])

<div @class([
    'hidden h-5 items-end gap-[2px] opacity-0 transition-opacity group-hover:opacity-60 md:flex' => $variant === 'row',
    'flex h-4 items-end gap-[2px]' => $variant !== 'row',
])>
    @foreach ($bars['durations'] as $duration)
        <span class="h-full {{ $bars['width'] }} origin-bottom animate-[podcast-equalize_var(--dur)_ease-in-out_infinite_alternate] rounded-full bg-[var(--podcast-color)] {{ $duration }} motion-reduce:animate-none"></span>
    @endforeach
</div>
