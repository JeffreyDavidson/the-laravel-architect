@props(['tech'])

<div {{ $attributes->class('about-value-card rounded-xl border border-gray-200 bg-white p-4 hover:border-brand-600/20 dark:border-[#1e2a3a] dark:bg-[#0D1117]/50 dark:hover:border-brand-600/20') }}>
    <span class="mb-1 block text-lg">{{ $tech['icon'] }}</span>
    <p class="mb-0.5 text-sm font-semibold text-gray-900 dark:text-white">{{ $tech['name'] }}</p>
    <p class="text-xs text-gray-500">{{ $tech['desc'] }}</p>
</div>
