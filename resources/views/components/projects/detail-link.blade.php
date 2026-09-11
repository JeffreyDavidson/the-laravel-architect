@props(['href', 'icon', 'label'])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener noreferrer"
    class="hover:text-brand-600 focus-visible:outline-brand-400 flex items-center gap-3 text-sm text-gray-600 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 dark:text-gray-400"
>
    <x-svg-icon :name="$icon" class="h-4 w-4 flex-shrink-0" />
    <span class="truncate">{{ $label }}</span>
    <x-svg-icon name="external-link" class="ml-auto h-3 w-3 flex-shrink-0" />
</a>
