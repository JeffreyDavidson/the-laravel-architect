<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <span>Shortcuts</span>
                <h3>Start the next thing</h3>
            </div>
        </div>

        <div class="tla-dashboard-actions">
            <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}" class="tla-dashboard-action">
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                </span>
                <span>
                    <strong>Write post</strong>
                    <small>Draft new Laravel article</small>
                </span>
            </a>

            <a href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('create') }}" class="tla-dashboard-action">
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--pink">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.25 6.75 21 10.5l-3.75 3.75M6.75 17.25 3 13.5l3.75-3.75M14.25 4.5l-4.5 15" /></svg>
                </span>
                <span>
                    <strong>Add project</strong>
                    <small>Showcase client or product work</small>
                </span>
            </a>

            <a href="{{ \App\Filament\Resources\Podcasts\PodcastResource::getUrl('create') }}" class="tla-dashboard-action">
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-12 0v1.5a6 6 0 0 0 6 6Zm0 0v3m-3 0h6M12 15a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v6a3 3 0 0 0 3 3Z" /></svg>
                </span>
                <span>
                    <strong>New podcast</strong>
                    <small>Plan a show or episode track</small>
                </span>
            </a>

            <a href="/" target="_blank" rel="noreferrer" class="tla-dashboard-action">
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5M15 3h6m0 0v6m0-6L10.5 13.5" /></svg>
                </span>
                <span>
                    <strong>View site</strong>
                    <small>Open the public homepage</small>
                </span>
            </a>
        </div>
    </section>
</x-filament-widgets::widget>
