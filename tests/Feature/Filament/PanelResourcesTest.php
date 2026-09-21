<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Insights;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Widgets\ContentPerformanceOverview;
use App\Filament\Widgets\ContentReadinessWidget;
use App\Filament\Widgets\EditorialOperationsOverview;
use App\Filament\Widgets\PublishingTrendsChart;
use App\Filament\Widgets\QuickLinksWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\WelcomeWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders each registered resource index for an authorized user', function (string $resource) {
    $url = $resource::getUrl('index');

    if (! is_string($url)) {
        throw new RuntimeException('The resource URL was not a string.');
    }

    $this->get($url)->assertOk();
})->with([
    CategoryResource::class,
    ContactInquiryResource::class,
    EpisodeResource::class,
    NewsletterIssueResource::class,
    PodcastResource::class,
    PostResource::class,
    ProjectResource::class,
    SubscriberResource::class,
    TagResource::class,
    VideoResource::class,
]);

it('registers visible navigation items for every admin section', function () {
    $navigation = Filament::getPanel('admin')->getNavigation();

    expect($navigation)->not->toBeEmpty();

    foreach ($navigation as $group) {
        expect($group->getItems())->not->toBeEmpty();
    }

    expect(collect($navigation)->map(fn (NavigationGroup $group): ?string => $group->getLabel())->all())
        ->toContain('Publish', 'Library', 'Audience', 'Operations');
});

it('renders the publishing dashboard for an authorized user', function () {
    $this->get(route('filament.admin.pages.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSeeHtml(Vite::asset('resources/js/filament/admin.js'))
        ->assertDontSeeHtml('const expandSidebarGroups');
});

it('keeps the dashboard focused on daily publishing work', function () {
    expect(app(Dashboard::class)->getWidgets())->toBe([
        WelcomeWidget::class,
        QuickLinksWidget::class,
        RecentActivityWidget::class,
        ContentReadinessWidget::class,
    ]);
});

it('moves reporting widgets to a dedicated insights page', function () {
    $this->get(Insights::getUrl())
        ->assertOk()
        ->assertSee('Insights')
        ->assertSee('without crowding the daily workspace');
});

it('protects long editing sessions and keeps the sidebar collapsible', function () {
    $panel = Filament::getPanel('admin');

    expect($panel->hasUnsavedChangesAlerts())->toBeTrue()
        ->and($panel->isSidebarCollapsibleOnDesktop())->toBeTrue()
        ->and($panel->hasCollapsibleNavigationGroups())->toBeTrue()
        ->and(CreateRecord::$formActionsAreSticky)->toBeTrue()
        ->and(EditRecord::$formActionsAreSticky)->toBeTrue();
});

it('registers the private analytics widgets for the insights page', function () {
    expect(Filament::getWidgets())
        ->toContain(ContentPerformanceOverview::class)
        ->toContain(EditorialOperationsOverview::class)
        ->toContain(PublishingTrendsChart::class);
});
