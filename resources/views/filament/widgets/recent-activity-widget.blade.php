<x-filament-widgets::widget>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full" style="background: linear-gradient(180deg, #4A7FBF, #c74b7a);"></span>
            Recent Activity
        </h3>
        @if($activities->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No activity yet. Start creating content!</p>
        @else
            <div class="space-y-3">
                @foreach($activities as $activity)
                <div class="flex items-start gap-3">
                    <span class="text-base mt-0.5 flex-shrink-0">{{ $activity['icon'] }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $activity['label'] }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs font-medium {{ $activity['color'] }}">{{ $activity['meta'] }}</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $activity['time'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
