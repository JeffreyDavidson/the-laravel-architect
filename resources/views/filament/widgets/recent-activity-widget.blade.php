<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <span>Latest movement</span>
                <h3>Recent activity</h3>
            </div>
        </div>

        @if($activities->isEmpty())
            <div class="tla-dashboard-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <strong>No activity yet</strong>
                    <span>Published posts and testimonial reviews will show up here.</span>
                </div>
            </div>
        @else
            <div class="tla-dashboard-timeline">
                @foreach($activities as $activity)
                    <a href="{{ $activity['url'] }}" class="tla-dashboard-timeline__item">
                        <div class="tla-dashboard-timeline__marker {{ $activity['meta'] === 'Published' || $activity['meta'] === 'Approved' ? 'is-good' : ($activity['meta'] === 'Pending Review' ? 'is-warning' : 'is-muted') }}">
                            @if($activity['kind'] === 'post')
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m0 12.75h7.5m-7.5 3h4.5m-4.5-15H9A2.25 2.25 0 0 1 11.25 6v1.5A2.25 2.25 0 0 0 13.5 9.75H15a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 15 21.75H6.75A2.25 2.25 0 0 1 4.5 19.5V5.25A2.25 2.25 0 0 1 6.75 3Z" /></svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-9.75 1.5c0 1.6 1.123 2.994 2.707 3.227 1.086.16 2.185.283 3.293.369V21l4.184-4.184c.166-.166.391-.26.626-.261 1.573-.013 3.14-.13 4.683-.352 1.584-.227 2.707-1.626 2.707-3.228V6.75c0-1.602-1.123-3.001-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.522C3.373 3.75 2.25 5.148 2.25 6.75v6Z" /></svg>
                            @endif
                        </div>

                        <div class="tla-dashboard-timeline__body">
                            <p>{{ $activity['label'] }}</p>
                            <div>
                                <span>{{ $activity['meta'] }}</span>
                                <small>{{ $activity['time'] }}</small>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
