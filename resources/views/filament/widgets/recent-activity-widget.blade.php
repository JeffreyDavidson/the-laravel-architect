<x-filament-widgets::widget>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-3 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full" style="background: linear-gradient(180deg, #4A7FBF, #c74b7a);"></span>
            Recent Activity
        </h3>
        @if($activities->isEmpty())
            <div class="flex items-center gap-3 py-4 justify-center">
                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="text-left">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No activity yet</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Start creating content to see updates here</p>
                </div>
            </div>
        @else
            <div class="divide-y divide-gray-950/5 dark:divide-white/5">
                @foreach($activities as $activity)
                <div class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full flex-shrink-0
                        @if($activity['meta'] === 'Published' || $activity['meta'] === 'Approved') bg-emerald-500/10
                        @elseif($activity['meta'] === 'Pending Review') bg-amber-500/10
                        @elseif($activity['meta'] === 'Rejected') bg-red-500/10
                        @else bg-gray-100 dark:bg-white/10
                        @endif">
                        @if($activity['meta'] === 'Published' || $activity['meta'] === 'Approved')
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($activity['meta'] === 'Draft')
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        @elseif($activity['meta'] === 'Pending Review')
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @else
                            <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $activity['label'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider
                            @if($activity['meta'] === 'Published' || $activity['meta'] === 'Approved') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                            @elseif($activity['meta'] === 'Draft') bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400
                            @elseif($activity['meta'] === 'Pending Review') bg-amber-500/10 text-amber-600 dark:text-amber-400
                            @else bg-red-500/10 text-red-600 dark:text-red-400
                            @endif
                        ">{{ $activity['meta'] }}</span>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ $activity['time'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
