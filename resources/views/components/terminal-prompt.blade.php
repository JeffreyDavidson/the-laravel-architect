@props(['command'])

<div class="mb-4 flex items-center gap-3">
    <div class="flex items-center gap-2 font-mono text-sm text-gray-500">
        <span class="text-brand-600">$</span>
        <span>php artisan {{ $command }}</span>
        <span class="dark:text-brand-600 relative -top-px text-gray-400" aria-hidden="true">▊</span>
    </div>
</div>
