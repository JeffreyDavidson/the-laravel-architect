{{-- A bordered card with a small uppercase heading, such as the podcast episode sidebar cards. Pass the padding and any outer spacing as classes. --}}
@props(['title'])

<div {{ $attributes->class('dark:border-surface-border dark:bg-surface-control rounded-2xl border border-gray-200 bg-white') }}>
    <h3 class="mb-4 text-xs font-semibold tracking-widest text-gray-500 uppercase">{{ $title }}</h3>
    {{ $slot }}
</div>
