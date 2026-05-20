@props(['filters' => [
    ['key' => 'all', 'label' => 'All Projects'],
    ['key' => 'featured', 'label' => '⭐ Featured'],
    ['key' => 'opensource', 'label' => 'Open Source'],
    ['key' => 'client', 'label' => 'Client Work'],
]])

{{-- Mobile: All Projects full width, then 3-col below --}}
<div class="sm:hidden space-y-2">
    <button
        @click="filter = 'all'"
        :class="filter === 'all' ? 'active' : ''"
        class="filter-tab w-full rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900 dark:border-brand-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-white"
    >
        All Projects
    </button>
    <div class="grid grid-cols-3 gap-2">
        @foreach(['featured' => '⭐ Featured', 'opensource' => 'Open Source', 'client' => 'Client Work'] as $key => $label)
            <button
                @click="filter = '{{ $key }}'"
                :class="filter === '{{ $key }}' ? 'active' : ''"
                class="filter-tab rounded-lg border border-gray-200 px-2 py-2 text-center text-xs font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900 dark:border-brand-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-white"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>

{{-- Desktop: single row --}}
<div class="hidden sm:flex gap-2">
    @foreach($filters as $filter)
        <button
            @click="filter = '{{ $filter['key'] }}'"
            :class="filter === '{{ $filter['key'] }}' ? 'active' : ''"
            class="filter-tab whitespace-nowrap rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:text-gray-900 dark:border-brand-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-white"
        >
            {{ $filter['label'] }}
        </button>
    @endforeach
</div>
