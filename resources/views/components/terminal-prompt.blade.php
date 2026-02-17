@props(['command'])

<div class="flex items-center gap-3 mb-4">
    <div class="font-mono text-sm text-gray-500 flex items-center gap-2">
        <span class="text-[#4A7FBF]">$</span>
        <span>php artisan {{ $command }}</span>
        <span class="animate-pulse text-gray-400 dark:text-[#4A7FBF] relative -top-px">▊</span>
    </div>
</div>
