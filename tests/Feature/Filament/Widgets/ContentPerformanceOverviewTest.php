<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Widgets\ContentPerformanceOverview;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('summarizes published content and audience data for administrators', function () {
    $administrator = User::factory()->create(['is_admin' => true]);

    Post::factory()
        ->published()
        ->create();
    Post::factory()->create();
    Episode::factory()
        ->published()
        ->create();
    NewsletterIssue::factory()
        ->published()
        ->create();
    Subscriber::factory()->create();
    Subscriber::factory()
        ->pending()
        ->create();
    Subscriber::factory()
        ->unsubscribed()
        ->create();
    Podcast::factory()
        ->inactive()
        ->create();
    Video::factory()->create(['view_count' => 1234]);

    actingAs($administrator);

    $widget = new class extends ContentPerformanceOverview
    {
        /** @return list<Stat> */
        public function stats(): array
        {
            return $this->getStats();
        }
    };
    $stats = $widget->stats();

    expect($stats)->toHaveCount(6)
        ->and($stats[0]->getValue())
        ->toBe(1)
        ->and($stats[1]->getValue())
        ->toBe(1)
        ->and($stats[2]->getValue())
        ->toBe(1)
        ->and($stats[3]->getValue())
        ->toBe(1)
        ->and($stats[4]->getValue())
        ->toBe(1)
        ->and($stats[5]->getValue())
        ->toBe('1,234');

    livewire(ContentPerformanceOverview::class)
        ->assertSee('Content performance')
        ->assertSee('Published posts')
        ->assertSee('Published episodes')
        ->assertSee('Newsletter subscribers')
        ->assertSee('Published issues')
        ->assertSee('Active podcasts')
        ->assertSee('YouTube views')
        ->assertSee('1,234')
        ->assertSeeHtml('href="'.PostResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.EpisodeResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.NewsletterIssueResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.PodcastResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.SubscriberResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.VideoResource::getUrl('index').'"');
});
