@props(['class' => ''])

<div class="dot-grid-bg bg-gray-50 dark:bg-transparent {{ $class }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        {{ $slot }}
    </div>
</div>
