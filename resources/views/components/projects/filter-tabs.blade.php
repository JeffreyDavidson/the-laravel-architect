@props(['filters' => [
    ['key' => 'all', 'label' => 'All Projects'],
    ['key' => 'featured', 'label' => '⭐ Featured'],
    ['key' => 'opensource', 'label' => 'Open Source'],
    ['key' => 'client', 'label' => 'Client Work'],
]])

<div class="flex flex-wrap gap-2">
    @foreach($filters as $filter)
        <button
            @click="filter = '{{ $filter['key'] }}'"
            :class="filter === '{{ $filter['key'] }}' ? 'active' : ''"
            class="filter-tab px-4 py-2 text-sm font-medium rounded-lg border border-[#1e2a3a] text-gray-400 hover:text-white hover:border-gray-600"
        >
            {{ $filter['label'] }}
        </button>
    @endforeach
</div>
