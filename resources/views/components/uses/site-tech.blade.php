@props(['tech'])

<div {{ $attributes->class('border-b border-gray-200 p-4 transition-colors hover:bg-white dark:border-[#1e2a3a] dark:hover:bg-[#111820] sm:border-r') }}>
    <div class="mb-1 flex items-center gap-2">
        <span class="text-base">{{ $tech['icon'] }}</span>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $tech['name'] }}</p>
    </div>
    <p class="text-xs text-gray-500">{{ $tech['desc'] }}</p>
</div>
