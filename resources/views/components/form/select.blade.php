@props([
    'id',
    'name',
])

<select
    id="{{ $id }}"
    name="{{ $name }}"
    {{ $attributes->class('w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition-all focus:border-brand-600 focus:ring-2 focus:ring-brand-600/10 dark:border-[#1e2a3a] dark:bg-[#0D1117] dark:text-gray-200') }}
>
    {{ $slot }}
</select>
