<?php

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

it('shows panel dates in the configured display timezone', function () {
    config()->set('app.display_timezone', 'Europe/London');

    expect(FilamentTimezone::get())
        ->toBe('Europe/London');
});

it('links the user menu to the configured source repository', function () {
    $urls = collect(Filament::getPanel('admin')->getUserMenuItems())
        ->map(fn (Action $item): ?string => $item->getUrl())
        ->all();

    expect($urls)
        ->toContain(config()->string('app.repository_url'));
});

it('orders the sidebar by navigation group case and navigation sort', function () {
    actingAs(User::factory()->make(['is_admin' => true]));
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $sidebar = collect(Filament::getNavigation())
        ->mapWithKeys(fn (NavigationGroup $group): array => [
            (string) $group->getLabel() => collect($group->getItems())
                ->whereInstanceOf(NavigationItem::class)
                ->map(fn (NavigationItem $item): string => $item->getLabel())
                ->values()
                ->all(),
        ])
        ->all();

    expect($sidebar)->toBe([
        '' => ['Dashboard'],
        'Publish' => ['Editorial Calendar', 'Posts', 'Podcasts', 'Episodes', 'Newsletter Issues'],
        'Library' => ['Projects', 'Categories', 'Tags', 'Videos'],
        'Audience' => ['Social Profiles', 'Subscribers', 'Contact Inquiries'],
        'Operations' => ['Insights', 'Media Health'],
    ]);
});

it('opens the sidebar with a new post link', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $html = FilamentView::renderHook(PanelsRenderHook::SIDEBAR_NAV_START)
        ->toHtml();

    expect($html)
        ->toContain('class="tla-sidebar-primary"')
        ->toContain(e(PostResource::getUrl('create')))
        ->toContain('New post');
});

it('frames the login form with the studio kicker and the theme switcher', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $before = FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE)
        ->toHtml();
    $after = FilamentView::renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER)
        ->toHtml();

    expect($before)
        ->toContain('class="tla-auth-kicker"')
        ->toContain('Private studio access')
        ->and($after)
        ->toContain('class="tla-auth-theme-switcher"');
});
