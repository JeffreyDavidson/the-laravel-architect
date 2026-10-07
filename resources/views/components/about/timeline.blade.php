@props(['items'])

{{-- Vertical timeline on small and large screens. --}}
<div class="hidden flex-shrink-0 lg:block lg:w-80">
    <x-section-heading icon="clock" class="mb-8">Timeline</x-section-heading>
    <div class="space-y-6">
        @foreach ($items as $item)
            <x-about.timeline-item :item="$item" />
        @endforeach
    </div>
</div>

{{-- Horizontal timeline at the middle breakpoint. --}}
<div class="mt-16 hidden md:block lg:hidden">
    <h2 class="mb-10 text-center text-2xl font-extrabold text-gray-900 dark:text-white">Timeline</h2>
    <div class="relative">
        <div class="bg-brand-600/25 absolute top-1/2 right-0 left-0 h-px"></div>

        <div class="grid grid-cols-6 gap-2">
            @foreach ($items as $i => $item)
                <div class="relative flex flex-col items-center {{ $i % 2 === 0 ? 'pt-0 pb-20' : 'pt-20 pb-0' }}">
                    @if ($i % 2 === 0)
                        <div class="mb-4 text-center">
                            <span class="text-brand-600 text-xs font-bold">{{ $item['year'] }}</span>
                            <p class="mt-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $item['title'] }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
                        </div>
                    @endif

                    <div class="bg-brand-600 z-10 h-3 w-3 flex-shrink-0 rounded-full"></div>

                    @if ($i % 2 !== 0)
                        <div class="mt-4 text-center">
                            <span class="text-brand-600 text-xs font-bold">{{ $item['year'] }}</span>
                            <p class="mt-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $item['title'] }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Vertical timeline on mobile. --}}
<div class="mt-12 md:hidden">
    <x-section-heading icon="clock" class="mb-8">Timeline</x-section-heading>
    <div class="space-y-6">
        @foreach ($items as $item)
            <x-about.timeline-item :item="$item" />
        @endforeach
    </div>
</div>
