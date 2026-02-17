<div>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5 h-full">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full" style="background: linear-gradient(180deg, #4A7FBF, #c74b7a);"></span>
            Recent Activity
        </h3>
        @if($activities->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">No activity yet</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Start creating content!</p>
            </div>
        @else
            <div class="space-y-0.5">
                @foreach($activities as $activity)
                <div class="group flex items-start gap-3 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full
                        @if($activity['meta'] === 'Published') bg-emerald-500/10 @elseif($activity['meta'] === 'Pending Review') bg-amber-500/10 @else bg-gray-100 dark:bg-white/10 @endif
                        flex-shrink-0">
                        @if($activity['meta'] === 'Published')
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @elseif($activity['meta'] === 'Draft')
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        @elseif($activity['meta'] === 'Pending Review')
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @else
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate leading-snug">{{ $activity['label'] }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider
                                @if($activity['meta'] === 'Published') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                                @elseif($activity['meta'] === 'Draft') bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400
                                @elseif($activity['meta'] === 'Pending Review') bg-amber-500/10 text-amber-600 dark:text-amber-400
                                @elseif($activity['meta'] === 'Approved') bg-emerald-500/10 text-emerald-600 dark:text-emerald-400
                                @else bg-red-500/10 text-red-600 dark:text-red-400
                                @endif
                            ">{{ $activity['meta'] }}</span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $activity['time'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
