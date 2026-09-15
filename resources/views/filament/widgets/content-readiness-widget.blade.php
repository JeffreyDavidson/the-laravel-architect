<x-filament-widgets::widget>
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.18em] text-gray-500 uppercase dark:text-gray-400">
                    Content readiness
                </p>
                <h2 class="mt-1 text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    Finish the public details
                </h2>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Keep the showcase and podcast pages complete.</p>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="group hover:border-brand-400 hover:bg-brand-50 focus-visible:outline-brand-500 dark:hover:border-brand-500 dark:hover:bg-brand-950/40 rounded-xl border border-gray-200 p-4 transition focus-visible:outline-2 focus-visible:outline-offset-2 dark:border-gray-700"
                >
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $item['label'] }}</span>
                        <span
                            @class([
                                'inline-flex min-w-7 items-center justify-center rounded-full px-2 py-1 text-xs font-semibold',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' => $item['count'] === 0,
                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' => $item['count'] > 0,
                            ])
                        >{{ $item['count'] }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $item['description'] }}</p>
                    <span class="text-brand-700 dark:text-brand-300 mt-4 inline-flex items-center gap-1 text-sm font-semibold group-hover:underline">
                        {{ $item['count'] === 0 ? 'Ready to publish' : 'Review content' }}
                        <span aria-hidden="true" class="transition-transform group-hover:translate-x-0.5">→</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
