<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <h3>Recent activity</h3>
                <p>The latest edits across the studio.</p>
            </div>
        </div>

        @if ($activities->isEmpty())
            <div class="tla-dashboard-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <strong>No activity yet</strong>
                    <span>Recent post activity will show up here.</span>
                </div>
            </div>
        @else
            <div class="tla-dashboard-timeline">
                @foreach ($activities as $activity)
                    <a href="{{ $activity['url'] }}" class="tla-dashboard-timeline__item">
                        <div class="tla-dashboard-timeline__marker {{ $activity['status'] === 'Published' ? 'is-good' : ($activity['status'] === 'In Review' || $activity['status'] === 'Scheduled' ? 'is-warning' : 'is-muted') }}">
                            @if ($activity['kind'] === 'Post' || $activity['kind'] === 'Newsletter')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-4.5-15H9A2.25 2.25 0 0 1 11.25 6v1.5A2.25 2.25 0 0 0 13.5 9.75H15a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 15 21.75H6.75A2.25 2.25 0 0 1 4.5 19.5V5.25A2.25 2.25 0 0 1 6.75 3Z" /></svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Zm3.75 8.25 2.25-2.25 1.5 1.5 3.75-4.5" /></svg>
                            @endif
                        </div>

                        <div class="tla-dashboard-timeline__body">
                            <p>{{ $activity['label'] }}</p>
                            <div>
                                <span>{{ $activity['kind'] }} · {{ $activity['status'] }}</span>
                                <small>{{ $activity['time'] }}</small>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
