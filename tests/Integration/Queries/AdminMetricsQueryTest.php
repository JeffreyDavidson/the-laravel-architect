<?php

use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\Video;
use App\Queries\AdminMetricsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;

use function Pest\Laravel\freezeSecond;

pest()->use(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

it('counts posts at each stage of the publishing pipeline', function () {
    freezeSecond();
    Post::factory()
        ->count(3)
        ->create();
    Post::factory()
        ->count(2)
        ->inReview()
        ->create();
    Post::factory()
        ->published()
        ->create();
    Post::factory()
        ->scheduled()
        ->create();
    Post::factory()
        ->published()
        ->create()
        ->delete();

    $metrics = app(AdminMetricsQuery::class);

    expect([
        'total' => $metrics->totalPosts(),
        'drafts' => $metrics->draftPosts(),
        'inReview' => $metrics->postsInReview(),
        'published' => $metrics->publishedPosts(),
        'scheduled' => $metrics->scheduledPosts(),
    ])->toBe([
        'total' => 7,
        'drafts' => 3,
        'inReview' => 2,
        'published' => 1,
        'scheduled' => 1,
    ]);
});

it('counts only new contact inquiries', function () {
    ContactInquiry::factory()
        ->count(2)
        ->create(['status' => ContactInquiryStatus::New]);
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::InProgress]);
    ContactInquiry::factory()->create(['status' => ContactInquiryStatus::Resolved]);

    expect(app(AdminMetricsQuery::class)->newContactInquiries())->toBe(2);
});

it('counts only confirmed subscribers who still receive the newsletter', function () {
    Subscriber::factory()
        ->count(2)
        ->create();
    Subscriber::factory()
        ->pending()
        ->create();
    Subscriber::factory()
        ->unsubscribed()
        ->create();
    Subscriber::factory()
        ->suppressed()
        ->create();

    expect(app(AdminMetricsQuery::class)->activeSubscribers())->toBe(2);
});

it('counts live episodes of active shows and the unpublished episode and issue queues', function () {
    freezeSecond();
    $activePodcast = Podcast::factory()->create();
    $inactivePodcast = Podcast::factory()
        ->inactive()
        ->create();
    Episode::factory()
        ->for($activePodcast)
        ->published()
        ->create();
    Episode::factory()
        ->for($inactivePodcast)
        ->published()
        ->create();
    Episode::factory()
        ->for($activePodcast)
        ->scheduled()
        ->create();
    NewsletterIssue::factory()
        ->published()
        ->create();
    NewsletterIssue::factory()->create();
    NewsletterIssue::factory()
        ->scheduled()
        ->create();

    $metrics = app(AdminMetricsQuery::class);

    expect([
        'liveEpisodes' => $metrics->publishedEpisodesOfActivePodcasts(),
        'episodeQueue' => $metrics->unpublishedEpisodes(),
        'liveIssues' => $metrics->publishedNewsletterIssues(),
        'issueQueue' => $metrics->unpublishedNewsletterIssues(),
        'activePodcasts' => $metrics->activePodcasts(),
    ])->toBe([
        'liveEpisodes' => 1,
        'episodeQueue' => 1,
        'liveIssues' => 1,
        'issueQueue' => 2,
        'activePodcasts' => 1,
    ]);
});

it('totals the synced YouTube views', function () {
    Video::factory()->create(['view_count' => 1200]);
    Video::factory()->create(['view_count' => 34]);

    expect(app(AdminMetricsQuery::class)->youTubeViews())->toBe(1234);
});

it('counts an issue\'s deliveries and how many were sent', function () {
    $issue = NewsletterIssue::factory()
        ->sent()
        ->create();
    $issueId = $issue->getKey();
    NewsletterDelivery::factory()
        ->sent()
        ->create(['newsletter_issue_id' => $issueId]);
    NewsletterDelivery::factory()
        ->count(2)
        ->create(['newsletter_issue_id' => $issueId]);
    NewsletterDelivery::factory()
        ->sent()
        ->create();

    $deliveries = app(AdminMetricsQuery::class)->newsletterDeliveries($issue);

    expect($deliveries->total)->toBe(3)
        ->and($deliveries->delivered)
        ->toBe(1);
});

it('reports no deliveries for an issue that was never sent', function () {
    $issue = NewsletterIssue::factory()
        ->published()
        ->create();

    $deliveries = app(AdminMetricsQuery::class)->newsletterDeliveries($issue);

    expect($deliveries->total)->toBe(0)
        ->and($deliveries->delivered)
        ->toBe(0);
});

it('counts published content per month and ignores drafts and earlier months', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    Post::factory()
        ->published()
        ->create(['published_at' => '2026-09-10 12:00:00']);
    Post::factory()->create(['published_at' => '2026-09-11 12:00:00']);
    Episode::factory()
        ->published()
        ->create(['published_at' => '2026-08-20 12:00:00']);
    NewsletterIssue::factory()
        ->published()
        ->create(['published_at' => '2026-08-02 12:00:00']);
    NewsletterIssue::factory()
        ->published()
        ->create(['published_at' => '2026-07-31 12:00:00']);
    $months = [Carbon::parse('2026-08-01 00:00:00'), Carbon::parse('2026-09-01 00:00:00')];

    expect(app(AdminMetricsQuery::class)->publishedPerMonth($months))->toBe([
        'posts' => [0, 1],
        'episodes' => [1, 0],
        'newsletterIssues' => [1, 0],
    ]);
});

it('buckets published content by display timezone month', function () {
    config(['app.display_timezone' => 'America/New_York']);
    Date::setTestNow(Carbon::parse('2026-10-01 02:00:00'));
    foreach (['Before the window' => '2026-04-01 02:00:00', 'July evening' => '2026-08-01 02:00:00', 'August morning' => '2026-08-01 05:00:00'] as $title => $publishedAt) {
        Post::factory()
            ->published()
            ->create([
                'title' => $title,
                'published_at' => $publishedAt,
            ]);
    }
    $months = array_map(
        fn (string $month): Carbon => Carbon::parse("{$month}-01 00:00:00", 'America/New_York'),
        ['2026-04', '2026-05', '2026-06', '2026-07', '2026-08', '2026-09'],
    );

    expect(app(AdminMetricsQuery::class)->publishedPerMonth($months)['posts'])->toBe([0, 0, 0, 1, 1, 0]);
});
