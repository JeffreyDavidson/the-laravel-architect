<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\SourceReviewStatus;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\freezeSecond;

pest()->use(RefreshDatabase::class);

// Frozen so the review dates and the cutoff share one day, even across midnight.
beforeEach(function () {
    freezeSecond();
    config()->set('content.post_review_interval_days', 180);
});

it('works out whether a post source is due for review', function (?string $sourceUrl, ?int $reviewedDaysAgo, bool $due, SourceReviewStatus $status) {
    $post = PublishableFixtures::readyPost([
        'source_url' => $sourceUrl,
        'last_reviewed_at' => $reviewedDaysAgo === null ? null : today()->subDays($reviewedDaysAgo),
    ]);

    $dueIds = Post::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    expect($post->isReviewDue())
        ->toBe($due)
        ->and($post->sourceReviewStatus())
        ->toBe($status)
        ->and(in_array($post->id, $dueIds, true))
        ->toBe($due);
})->with([
    'no source' => [null, null, false, SourceReviewStatus::NotTracked],
    'source never reviewed' => ['https://laravel.com/docs', null, true, SourceReviewStatus::ReviewDue],
    'reviewed within the interval' => ['https://laravel.com/docs', 179, false, SourceReviewStatus::Current],
    'reviewed on the interval boundary' => ['https://laravel.com/docs', 180, false, SourceReviewStatus::Current],
    'reviewed past the interval' => ['https://laravel.com/docs', 181, true, SourceReviewStatus::ReviewDue],
]);

it('uses the configured review interval', function () {
    config()->set('content.post_review_interval_days', 30);
    $post = PublishableFixtures::readyPost([
        'source_url' => 'https://laravel.com/docs',
        'last_reviewed_at' => today()->subDays(31),
    ]);

    expect($post->isReviewDue())
        ->toBeTrue();
});
