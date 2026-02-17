@props(['icon' => null, 'class' => ''])

<h2 class="text-2xl font-extrabold flex items-center gap-3 {{ $class }}">
    @if($icon)
    <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
        <x-svg-icon :name="$icon" class="w-4 h-4 text-[#4A7FBF]" />
    </span>
    @endif
    {{ $slot }}
</h2>
