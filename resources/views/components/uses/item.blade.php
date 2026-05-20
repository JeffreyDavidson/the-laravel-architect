@props(['item'])

@php
    $classes = 'group flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-4 transition-all duration-200 hover:translate-x-1 hover:border-brand-600/15 hover:bg-brand-600/[0.02] dark:border-[#1e2a3a] dark:bg-[#0D1117] dark:hover:border-[#2a3a4a] dark:hover:bg-brand-600/[0.03]';
@endphp

@if(isset($item['url']))
    <a href="{{ $item['url'] }}" target="_blank" class="{{ $classes }}">
        @include('components.uses.partials.item-content', ['item' => $item, 'linked' => true])
    </a>
@else
    <div class="{{ $classes }}">
        @include('components.uses.partials.item-content', ['item' => $item, 'linked' => false])
    </div>
@endif
