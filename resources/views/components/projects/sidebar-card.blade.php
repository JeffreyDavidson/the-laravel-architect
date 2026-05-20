@props(['title'])

<div {{ $attributes->class('rounded-2xl border border-gray-200 bg-white p-6 dark:border-brand-700 dark:bg-brand-950') }}>
    <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-gray-500">{{ $title }}</h3>
    {{ $slot }}
</div>
