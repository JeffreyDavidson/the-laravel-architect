@props(['label'])

<span {{ $attributes->class('rounded-lg border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 transition-colors hover:border-brand-600/30 hover:bg-brand-600/10 hover:text-brand-600 dark:border-brand-700 dark:bg-brand-700/30 dark:text-gray-400 dark:hover:border-brand-400/30 dark:hover:bg-brand-600/10 dark:hover:text-brand-300') }}>
    {{ $label }}
</span>
