@props(['class' => ''])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 dark:border-surface-border bg-white dark:bg-surface-control ' . $class]) }}>
    {{ $slot }}
</div>
