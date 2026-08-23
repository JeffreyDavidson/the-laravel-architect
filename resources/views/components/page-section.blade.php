@props(['class' => ''])

<div class="bg-gray-50 dark:bg-[#0b1016] {{ $class }}">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-20 lg:px-8">
        {{ $slot }}
    </div>
</div>
