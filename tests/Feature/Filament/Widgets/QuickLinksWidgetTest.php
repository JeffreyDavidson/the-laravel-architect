<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
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
        ->assertSee('Add episode')
        ->assertSeeHtml('href="'.EpisodeResource::getUrl('create').'"')
        ->assertSee('Write newsletter')
        ->assertSeeHtml('href="'.NewsletterIssueResource::getUrl('create').'"');
});
