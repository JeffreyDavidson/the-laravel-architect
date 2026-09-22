<x-filament-widgets::widget>
    <section class="tla-dashboard-panel">
        <div class="tla-dashboard-panel__header">
            <div>
                <h3>Create content</h3>
                <p>Start with the right editor.</p>
            </div>
        </div>

        <div class="tla-dashboard-actions">
            @foreach ($items as $item)
                <x-filament.dashboard-action
                    :url="$item['url']"
                    :label="$item['label']"
                    :description="$item['description']"
                    :color="$item['color']"
                    :icon="$item['icon']"
                />
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
