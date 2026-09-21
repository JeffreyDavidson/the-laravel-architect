<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <h3>Create content</h3>
                <p>Start with the right editor.</p>
            </div>
        </div>

        <div class="tla-dashboard-actions">
            <a href="{{ \App\Filament\Resources\Posts\PostResource::getUrl('create') }}" class="tla-dashboard-action">
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                </span>
                <span>
                    <strong>Write post</strong>
                    <small>Draft a new Laravel article</small>
                </span>
            </a>

            <a
                href="{{ \App\Filament\Resources\Episodes\EpisodeResource::getUrl('create') }}"
                class="tla-dashboard-action"
            >
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-12 0v1.5a6 6 0 0 0 6 6Zm0 0v3m-3 0h6M12 15a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v6a3 3 0 0 0 3 3Z" /></svg>
                </span>
                <span>
                    <strong>Add episode</strong>
                    <small>Prepare audio and show notes</small>
                </span>
            </a>

            <a
                href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('create') }}"
                class="tla-dashboard-action"
            >
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--pink">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.25 6.75 21 10.5l-3.75 3.75M6.75 17.25 3 13.5l3.75-3.75M14.25 4.5l-4.5 15" /></svg>
                </span>
                <span>
                    <strong>Add project</strong>
                    <small>Document a case study</small>
                </span>
            </a>

            <a
                href="{{ \App\Filament\Resources\NewsletterIssues\NewsletterIssueResource::getUrl('create') }}"
                class="tla-dashboard-action"
            >
                <span class="tla-dashboard-action__icon tla-dashboard-action__icon--amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.69 5.52a2 2 0 0 1-2.12 0L2.25 6.75" /></svg>
                </span>
                <span>
                    <strong>Write newsletter</strong>
                    <small>Prepare the next issue</small>
                </span>
            </a>
        </div>
    </section>
</x-filament-widgets::widget>
