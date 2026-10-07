<?php

use App\Enums\ContactInquiryStatus;
use App\Filament\Widgets\EditorialOperationsOverview;
use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Laravel\get;

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

    $widget = new class extends EditorialOperationsOverview
    {
        /** @return list<Stat> */
        public function stats(): array
        {
            return $this->getStats();
        }
    };

    $stats = $widget->stats();

    expect($stats[0]->getValue())->toBe(1)
        ->and($stats[1]->getValue())
        ->toBe(1)
        ->and($stats[2]->getValue())
        ->toBe(1)
        ->and($stats[3]->getValue())
        ->toBe(0)
        ->and($stats[4]->getValue())
        ->toBe(1)
        ->and($stats[5]->getValue())
        ->toBe(1);
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
    $widget = new class extends EditorialOperationsOverview
    {
        /** @return list<Stat> */
        public function stats(): array
        {
            return $this->getStats();
        }
    };

    $stats = $widget->stats();

    expect($stats[3]->getValue())->toBe(2)
        ->and($stats[4]->getValue())
        ->toBe(2);

    foreach ([3 => 'episode', 4 => 'issue'] as $index => $type) {
        $url = $stats[$index]->getUrl();
        if (! is_string($url) || $url === '') {
            throw new RuntimeException("Missing {$type} queue URL.");
        }

        $response = get($url);

        $response->assertSuccessful()
            ->assertSee("Queue {$type} 1")
            ->assertSee("Queue {$type} 2")
            ->assertDontSee("Queue {$type} 0");
    }
});
