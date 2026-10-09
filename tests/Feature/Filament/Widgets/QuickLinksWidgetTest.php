<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Widgets\QuickLinksWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

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

it('renders with the dashboard instead of loading afterwards', function () {
    actingAs(
        User::factory()
            ->create(['is_admin' => true]),
    );

    get(Dashboard::getUrl())
        ->assertOk()
        ->assertSee('Write post');
});
