@props(['filters' => [
    ['key' => 'all', 'label' => 'All Projects'],
    ['key' => 'featured', 'label' => '⭐ Featured'],
    ['key' => 'opensource', 'label' => 'Open Source'],
    ['key' => 'client', 'label' => 'Client Work'],
]])

<div class="flex overflow-x-auto gap-2 pb-2 -mb-2 scrollbar-hide">
    @foreach($filters as $filter)
        <button
            @click="filter = '{{ $filter['key'] }}'"
            :class="filter === '{{ $filter['key'] }}' ? 'active' : ''"
            class="filter-tab px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg border border-gray-200 dark:border-[#1e2a3a] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:border-gray-400 dark:hover:border-gray-600 whitespace-nowrap flex-shrink-0"
        >
            {{ $filter['label'] }}
        </button>
    @endforeach
</div>
