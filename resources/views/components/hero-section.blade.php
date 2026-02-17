@props(['class' => ''])

<div class="noise-overlay relative overflow-hidden border-b border-gray-200 dark:border-[#1e2a3a] bg-white dark:bg-transparent {{ $class }}">
    <x-ambient-glow />
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="max-w-3xl">
            {{ $slot }}
        </div>
    </div>
</div>
