@props(['icon' => null, 'class' => ''])

<h2 class="text-2xl font-extrabold flex items-center gap-3 {{ $class }}">
    @if ($icon)
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#4A7FBF]/10">
            <x-svg-icon :name="$icon" class="h-4 w-4 text-[#4A7FBF]" />
        </span>
    @endif
    {{ $slot }}
</h2>
