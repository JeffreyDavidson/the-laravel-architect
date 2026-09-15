<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Plan posts and podcast episodes by publication date. Items without a date stay in the unscheduled
                    queue.
                </p>
                <h2 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                    {{ $calendarMonth->format('F Y') }}
                </h2>
            </div>

            <div class="flex flex-wrap gap-2 text-sm text-gray-500 dark:text-gray-400">
                @foreach ($statuses as $status => $count)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-2 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                        {{ $status }} ({{ $count }})
                    </span>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="min-w-[56rem]">
                <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-xs font-medium tracking-wide text-gray-500 uppercase dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                    @foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $weekday)
                        <div class="px-3 py-3 text-center">{{ $weekday }}</div>
                    @endforeach
                </div>

                @foreach ($weeks as $week)
                    <div class="grid grid-cols-7 divide-x divide-gray-200 border-b border-gray-200 last:border-b-0 dark:divide-white/10 dark:border-white/10">
                        @foreach ($week as $day)
                            <div @class([
                                'min-h-32 space-y-2 p-2',
                                'bg-gray-50/70 dark:bg-white/[0.02]' => ! $day['isCurrentMonth'],
                            ])>
                                <div @class([
                                    'flex size-7 items-center justify-center rounded-full text-sm',
                                    'font-semibold text-primary-600 ring-1 ring-primary-600 dark:text-primary-400' => $day['isToday'],
                                    'text-gray-950 dark:text-white' => $day['isCurrentMonth'] && ! $day['isToday'],
                                    'text-gray-400 dark:text-gray-600' => ! $day['isCurrentMonth'],
                                ])>
                                    {{ $day['day'] }}
                                </div>

                                <div class="space-y-1.5">
                                    @foreach ($day['entries'] as $entry)
                                        <a
                                            href="{{ $entry['url'] }}"
                                            wire:navigate
                                            class="group hover:border-primary-500 hover:ring-primary-500 block rounded-lg border border-gray-200 bg-white p-2 shadow-xs transition hover:ring-1 dark:border-white/10 dark:bg-white/5"
                                        >
                                            <div class="flex items-center justify-between gap-1 text-[10px] font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                                <span>{{ $entry['type'] }}</span>
                                                <x-filament::badge :color="$entry['statusColor']">
                                                    {{ $entry['statusLabel'] }}
                                                </x-filament::badge>
                                            </div>
                                            <p class="group-hover:text-primary-600 dark:group-hover:text-primary-400 mt-1 line-clamp-2 text-xs font-medium text-gray-950 dark:text-white">
                                                {{ $entry['title'] }}
                                            </p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        @if ($unscheduled->isNotEmpty())
            <x-filament::section
                heading="Unscheduled content"
                description="Content that still needs a publication date."
            >
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($unscheduled as $entry)
                        <a
                            href="{{ $entry['url'] }}"
                            wire:navigate
                            class="group hover:border-primary-500 hover:ring-primary-500 rounded-lg border border-gray-200 bg-white p-3 shadow-xs transition hover:ring-1 dark:border-white/10 dark:bg-white/5"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400">
                                    {{ $entry['type'] }}
                                </span>
                                <x-filament::badge :color="$entry['statusColor']">
                                    {{ $entry['statusLabel'] }}
                                </x-filament::badge>
                            </div>
                            <p class="group-hover:text-primary-600 dark:group-hover:text-primary-400 mt-2 font-medium text-gray-950 dark:text-white">
                                {{ $entry['title'] }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
