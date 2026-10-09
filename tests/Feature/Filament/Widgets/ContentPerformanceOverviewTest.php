<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Widgets\ContentPerformanceOverview;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\SubscriberResource;
use Tests\Support\RenderedStats;

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

    $widget = livewire(ContentPerformanceOverview::class);

    $widget->assertSee('Content performance');
    expect(RenderedStats::from($widget->html()))->toBe([
        'Published posts' => ['value' => '1', 'url' => PostResource::getUrl('index')],
        'Published episodes' => ['value' => '1', 'url' => EpisodeResource::getUrl('index')],
        'Newsletter subscribers' => ['value' => '1', 'url' => SubscriberResource::getUrl('index')],
        'Published issues' => ['value' => '1', 'url' => NewsletterIssueResource::getUrl('index')],
        'Active podcasts' => ['value' => '1', 'url' => PodcastResource::getUrl('index')],
        'YouTube views' => ['value' => '1,234', 'url' => VideoResource::getUrl('index')],
    ]);
});
