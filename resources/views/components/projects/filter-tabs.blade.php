@props(['filters' => [
    ['key' => 'all', 'label' => 'All Projects'],
    ['key' => 'featured', 'label' => '⭐ Featured'],
    ['key' => 'opensource', 'label' => 'Open Source'],
    ['key' => 'client', 'label' => 'Client Work'],
]])

<div class="sm:flex sm:gap-2 sm:pb-2 sm:-mb-2">
    {{-- All Projects: full width on mobile --}}
    <button
        @click="filter = 'all'"
        :class="filter === 'all' ? 'active' : ''"
        class="filter-tab w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-400 dark:hover:border-gray-600 whitespace-nowrap mb-2 sm:mb-0"
    >
        All Projects
    </button>

    {{-- Other filters: 3-column grid on mobile --}}
    <div class="grid grid-cols-3 gap-2 sm:contents">
        @foreach(['featured' => '⭐ Featured', 'opensource' => 'Open Source', 'client' => 'Client Work'] as $key => $label)
            <button
                @click="filter = '{{ $key }}'"
                :class="filter === '{{ $key }}' ? 'active' : ''"
                class="filter-tab px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-400 dark:hover:border-gray-600 whitespace-nowrap"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>
