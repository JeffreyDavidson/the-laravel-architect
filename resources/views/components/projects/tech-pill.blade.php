@props(['label'])

<span {{ $attributes->class('tech-pill rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 dark:border-brand-700 dark:bg-brand-700/30 dark:text-gray-400') }}>
    {{ $label }}
</span>
