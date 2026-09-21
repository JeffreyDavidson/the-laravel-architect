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
                @foreach ($items as $index => $item)
                    <a href="{{ $item['url'] }}" class="tla-dashboard-action">
                        <span
                            @class([
                                'tla-dashboard-action__icon',
                                'tla-dashboard-action__icon--blue' => $index === 0,
                                'tla-dashboard-action__icon--pink' => $index === 1,
                                'tla-dashboard-action__icon--green' => $index === 2,
                                'tla-dashboard-action__icon--amber' => $index === 3,
                            ])
                        >{{ $item['count'] }}</span>
                        <span>
                            <strong>{{ $item['label'] }}</strong>
                            <small>{{ $item['description'] }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
