@props(['icon', 'label', 'value'])

<div>
    <span class="mb-1 block text-2xl">{{ $icon }}</span>
    <p class="text-xs uppercase tracking-wider text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $value }}</p>
</div>
