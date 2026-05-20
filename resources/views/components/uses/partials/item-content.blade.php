<span class="mt-0.5 flex-shrink-0 text-xl">{{ $item['icon'] }}</span>
<div class="min-w-0 flex-1">
    <div class="flex items-center gap-2">
        <h3 class="text-sm font-semibold transition-colors group-hover:text-brand-600">{{ $item['name'] }}</h3>
        @if($linked)
            <svg class="h-3 w-3 text-gray-600 opacity-0 transition-opacity group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        @endif
    </div>
    <p class="mt-0.5 text-xs text-gray-500">{{ $item['desc'] }}</p>
</div>
<span class="mt-1 flex-shrink-0 font-mono text-[10px] text-gray-600">{{ $item['tag'] }}</span>
