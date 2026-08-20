@props(['tech'])

<div {{ $attributes->class('rounded-xl border border-gray-200 bg-gray-50 p-4 transition-[border-color,background-color,box-shadow] duration-200 hover:border-brand-600/25 hover:bg-brand-600/[0.02] hover:shadow-sm dark:border-[#1e2a3a] dark:bg-[#0D1117]/50 dark:hover:bg-brand-600/[0.03]') }}>
    <div class="mb-1 flex items-center gap-2">
        <span class="text-base">{{ $tech['icon'] }}</span>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $tech['name'] }}</p>
    </div>
    <p class="text-[11px] text-gray-500">{{ $tech['desc'] }}</p>
</div>
