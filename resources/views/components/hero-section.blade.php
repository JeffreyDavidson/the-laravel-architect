@props(['class' => ''])

<header class="border-b border-gray-200 bg-white dark:border-[#1e2a3a] dark:bg-[#0b1016] {{ $class }}">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
        <div class="max-w-3xl">
            {{ $slot }}
        </div>
    </div>
</header>
