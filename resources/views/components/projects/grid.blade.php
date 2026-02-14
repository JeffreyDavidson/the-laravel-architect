@props(['title' => '', 'show' => 'true'])

<div x-show="{{ $show }}" x-transition {{ $attributes->merge(['class' => '']) }}>
    @if($title)
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-6">{{ $title }}</h2>
    @endif

    {{ $slot }}
</div>
