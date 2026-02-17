@props(['class' => ''])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-[#0D1117] ' . $class]) }}>
    {{ $slot }}
</div>
