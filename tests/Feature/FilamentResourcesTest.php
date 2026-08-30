<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders each registered resource index for an authorized user', function (string $resource) {
    $this->get($resource::getUrl('index'))->assertOk();
})->with([
    CategoryResource::class,
    EpisodeResource::class,
    PodcastResource::class,
    PostResource::class,
    ProjectResource::class,
    SubscriberResource::class,
    TagResource::class,
    TestimonialResource::class,
    VideoResource::class,
]);

it('registers visible navigation items for every admin section', function () {
    $navigation = Filament::getPanel('admin')->getNavigation();

    expect($navigation)->not->toBeEmpty();

    foreach ($navigation as $group) {
        expect($group->getItems())->not->toBeEmpty();
    }

    expect(collect($navigation)->map(fn ($group): ?string => $group->getLabel())->all())
        ->toContain('Content', 'Podcasting', 'Showcase', 'Taxonomy', 'Newsletter', 'YouTube');
});

it('renders the publishing dashboard for an authorized user', function () {
    $this->get(route('filament.admin.pages.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee(Vite::asset('resources/js/filament/admin.js'), false)
        ->assertDontSee('const expandSidebarGroups', false);
});
