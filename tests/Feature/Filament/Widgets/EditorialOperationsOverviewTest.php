<?php

use App\Enums\ContactInquiryStatus;
use App\Enums\PublishStatus;
use App\Filament\Widgets\EditorialOperationsOverview;
use App\Models\ContactInquiry;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('summarizes first-party editorial work and links', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user);

    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::New]);
    Post::query()->create([
        'title' => 'Post in review',
        'excerpt' => 'An excerpt.',
        'content' => 'Content.',
        'status' => PublishStatus::InReview,
        'user_id' => $user->id,
    ]);
    Post::query()->create([
        'title' => 'Scheduled post',
        'excerpt' => 'An excerpt.',
        'content' => 'Content.',
        'status' => PublishStatus::Scheduled,
        'user_id' => $user->id,
    ]);
    NewsletterIssue::query()->create([
        'title' => 'Draft issue',
        'excerpt' => 'An excerpt.',
        'content' => 'Content.',
        'status' => PublishStatus::Draft,
    ]);
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

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
        ->and($stats[1]->getValue())->toBe(1)
        ->and($stats[2]->getValue())->toBe(1)
        ->and($stats[3]->getValue())->toBe(0)
        ->and($stats[4]->getValue())->toBe(1)
        ->and($stats[5]->getValue())->toBe(1);
});
