@props(['icon' => null])

<h2 {{ $attributes->class('flex items-center gap-3 text-2xl font-extrabold') }}>
    @if ($icon)
        <span class="bg-brand-600/10 flex h-8 w-8 items-center justify-center rounded-lg">
            <x-svg-icon :name="$icon" class="text-brand-600 h-4 w-4" />
        </span>
    @endif
    {{ $slot }}
</h2>
