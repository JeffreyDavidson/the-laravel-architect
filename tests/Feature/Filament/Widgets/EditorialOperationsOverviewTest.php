<?php

use App\Filament\Widgets\EditorialOperationsOverview;
use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;
use JeffreyDavidson\CreatorKit\Enums\PublicationState;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\EpisodeResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\PostResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Subscribers\SubscriberResource;
use Tests\Support\RenderedStats;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('summarizes first-party editorial work and links', function () {
    $user = User::factory()->create(['is_admin' => true]);

    actingAs($user);

    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    Post::factory()
        ->inReview()
        ->create();
    Post::factory()
        ->scheduled()
        ->create();
    NewsletterIssue::factory()->create();
    Subscriber::factory()->create();

    $html = livewire(EditorialOperationsOverview::class)->html();

    expect(RenderedStats::from($html))->toBe([
        'New inquiries' => ['value' => '1', 'url' => ContactInquiryResource::getUrl('index')],
        'Posts in review' => ['value' => '1', 'url' => PostResource::getUrl('index')],
        'Scheduled posts' => ['value' => '1', 'url' => PostResource::getUrl('index', ['filters' => ['publication' => ['value' => PublicationState::Scheduled->value]]])],
        'Episode queue' => ['value' => '0', 'url' => EpisodeResource::getUrl('index', ['filters' => ['publication' => ['value' => PublicationState::Unpublished->value]]])],
        'Newsletter queue' => ['value' => '1', 'url' => NewsletterIssueResource::getUrl('index', ['tab' => 'unpublished'])],
        'Active subscribers' => ['value' => '1', 'url' => SubscriberResource::getUrl('index')],
    ]);
});

it('excludes already live scheduled content from unpublished operational queues', function () {
    freezeSecond();
    actingAs(User::factory()->create(['is_admin' => true]));
    $podcast = Podcast::factory()->create();
    foreach ([now()->subMinute(), now()->addDay(), null] as $index => $date) {
        Episode::factory()
            ->for($podcast)
            ->scheduled()
            ->create(['title' => "Queue episode {$index}", 'published_at' => $date]);
        NewsletterIssue::factory()
            ->scheduled()
            ->create(['title' => "Queue issue {$index}", 'published_at' => $date]);
    }

    $stats = RenderedStats::from(livewire(EditorialOperationsOverview::class)->html());

    expect($stats['Episode queue']['value'])->toBe('2')
        ->and($stats['Newsletter queue']['value'])
        ->toBe('2');

    foreach (['Episode queue' => 'episode', 'Newsletter queue' => 'issue'] as $label => $type) {
        $url = $stats[$label]['url'];
        if ($url === null || $url === '') {
            throw new RuntimeException("Missing {$type} queue URL.");
        }

        $response = get($url);

        $response->assertSuccessful()
            ->assertSee("Queue {$type} 1")
            ->assertSee("Queue {$type} 2")
            ->assertDontSee("Queue {$type} 0");
    }
});
