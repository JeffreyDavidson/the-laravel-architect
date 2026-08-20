<?php

use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Widgets\QuickLinksWidget;

use function Pest\Livewire\livewire;

it('renders shortcuts to common content actions', function () {
    livewire(QuickLinksWidget::class)
        ->assertSee('Write post')
        ->assertSeeHtml('href="'.PostResource::getUrl('create').'"')
        ->assertSee('Add project')
        ->assertSeeHtml('href="'.ProjectResource::getUrl('create').'"')
        ->assertSee('New podcast')
        ->assertSeeHtml('href="'.PodcastResource::getUrl('create').'"')
        ->assertSee('View site')
        ->assertSeeHtml('href="/"');
});
