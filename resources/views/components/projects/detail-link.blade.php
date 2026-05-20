@props(['href', 'icon', 'label'])

<a href="{{ $href }}" target="_blank" class="flex items-center gap-3 text-sm text-gray-600 transition-colors hover:text-brand-600 dark:text-gray-400">
    <x-svg-icon :name="$icon" class="h-4 w-4 flex-shrink-0" />
    <span class="truncate">{{ $label }}</span>
    <x-svg-icon name="external-link" class="ml-auto h-3 w-3 flex-shrink-0" />
</a>
