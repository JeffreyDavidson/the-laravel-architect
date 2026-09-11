@props(['command'])

<div class="mb-4 flex items-center gap-3">
    <div class="flex items-center gap-2 font-mono text-sm text-gray-500">
        <span class="text-[#4A7FBF]">$</span>
        <span>php artisan {{ $command }}</span>
        <span class="relative -top-px text-gray-400 dark:text-[#4A7FBF]">▊</span>
    </div>
</div>
