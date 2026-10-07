<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <h3>Needs finishing</h3>
                <p>Only incomplete public details are shown here.</p>
            </div>
            @if ($outstandingCount > 0)
                <strong>{{ $outstandingCount }} open {{ $outstandingCount === 1 ? 'item' : 'items' }}</strong>
            @endif
        </div>

        @if ($items === [])
            <div class="tla-dashboard-empty tla-dashboard-empty--success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <div>
                    <strong>Everything is ready</strong>
                    <span>The public-facing content checks are complete.</span>
                </div>
            </div>
        @else
            <div class="tla-dashboard-actions">
                {{-- The widget shows at most four areas, one badge colour each. --}}
                @foreach ($items as $index => $item)
                    <x-filament.dashboard-action
                        :url="$item['url']"
                        :label="$item['label']"
                        :description="$item['description']"
                        :color="['blue', 'pink', 'green', 'amber'][$index]"
                    >
                        {{ $item['count'] }}
                    </x-filament.dashboard-action>
                @endforeach
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
