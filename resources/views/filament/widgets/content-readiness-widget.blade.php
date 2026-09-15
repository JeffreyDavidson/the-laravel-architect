<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <span>Content readiness</span>
                <h3>Finish the public details</h3>
            </div>
            <span>Keep the showcase and podcast pages complete.</span>
        </div>

        <div class="tla-dashboard-actions">
            @foreach ($items as $index => $item)
                <a href="{{ $item['url'] }}" class="tla-dashboard-action">
                    <span
                        @class([
                            'tla-dashboard-action__icon',
                            'tla-dashboard-action__icon--blue' => in_array($index, [0, 4], true),
                            'tla-dashboard-action__icon--pink' => in_array($index, [1, 5], true),
                            'tla-dashboard-action__icon--green' => in_array($index, [2, 6], true),
                            'tla-dashboard-action__icon--amber' => $index === 3,
                        ])
                    >{{ $item['count'] }}</span>
                    <span>
                        <strong>{{ $item['label'] }}</strong>
                        <small>{{ $item['description'] }}</small>
                        <small>{{ $item['count'] === 0 ? 'Ready to publish' : 'Review content' }}</small>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
