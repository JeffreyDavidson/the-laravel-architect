<?php

use App\Enums\ContentReadinessArea;
use App\Enums\ProjectReadinessFilter;
use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Video;
use App\Publishing\ContentReadiness;
use App\Publishing\ContentReadinessCriteria;
use App\Publishing\ContentReadinessSummaryQuery;
use App\Publishing\ProjectReadinessCriteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    createContentReadinessAgreementFixtures();
});

it('gives the same verdict as ContentReadiness for every check of every content type', function (string $modelClass) {
    /** @var class-string<Post|Project|Podcast|Episode|NewsletterIssue|Video> $modelClass */
    $criteria = app(ContentReadinessCriteria::class);
    $checks = $criteria->checks(new $modelClass);

    expect($checks)->toBe(new ContentReadiness($modelClass::query()->firstOrFail())->checks());

    foreach ($checks as $check) {
        $incomplete = $modelClass::query();
        $criteria->whereIncomplete($incomplete, [$check]);
        $complete = $modelClass::query();
        $criteria->whereComplete($complete, [$check]);

        expect(contentReadinessQueryIds($incomplete))
            ->toBe(contentReadinessVerdictIds($modelClass::query(), fn (ContentReadiness $readiness): bool => ! $readiness->isComplete($check)), "SQL and PHP disagree on incomplete [{$check->value}]")
            ->and(contentReadinessQueryIds($complete))
            ->toBe(contentReadinessVerdictIds($modelClass::query(), fn (ContentReadiness $readiness): bool => $readiness->isComplete($check)), "SQL and PHP disagree on complete [{$check->value}]");
    }

    $ready = $modelClass::query();
    $criteria->whereReady($ready);

    expect(contentReadinessQueryIds($ready))
        ->toBe(contentReadinessVerdictIds($modelClass::query(), fn (ContentReadiness $readiness): bool => $readiness->isReady()))
        ->not->toBeEmpty();
})->with([
    'posts' => [Post::class],
    'projects' => [Project::class],
    'podcasts' => [Podcast::class],
    'episodes' => [Episode::class],
    'newsletter issues' => [NewsletterIssue::class],
    'videos' => [Video::class],
]);

it('counts the same incomplete records as ContentReadiness for each dashboard area', function (ContentReadinessArea $area) {
    $summaryQuery = app(ContentReadinessSummaryQuery::class);
    $expectedIds = contentReadinessVerdictIds(
        $summaryQuery->records($area),
        fn (ContentReadiness $readiness): bool => ! array_all($area->checks(), $readiness->isComplete(...)),
    );

    expect(contentReadinessQueryIds($summaryQuery->incomplete($area)))
        ->toBe($expectedIds)
        ->and($summaryQuery->count($area))
        ->toBe(count($expectedIds))
        ->toBeGreaterThan(0);
})->with(ContentReadinessArea::cases());

it('filters the same projects as ContentReadiness for each project readiness filter', function (ProjectReadinessFilter $filter, Closure $verdict) {
    /** @var Closure(ContentReadiness): bool $verdict */
    $query = Project::query();
    app(ProjectReadinessCriteria::class)->apply($query, $filter->value);

    expect(contentReadinessQueryIds($query))
        ->toBe(contentReadinessVerdictIds(Project::query(), $verdict))
        ->not->toBeEmpty();
})->with([
    'ready' => [ProjectReadinessFilter::Ready, fn (ContentReadiness $readiness): bool => $readiness->isReady()],
    'needs image' => [ProjectReadinessFilter::NeedsImage, fn (ContentReadiness $readiness): bool => ! $readiness->isComplete(ReadinessCheck::FeaturedImage)],
    'needs case study' => [ProjectReadinessFilter::NeedsCaseStudy, fn (ContentReadiness $readiness): bool => ! $readiness->isComplete(ReadinessCheck::CaseStudy)],
    'needs details' => [ProjectReadinessFilter::NeedsDetails, fn (ContentReadiness $readiness): bool => ! array_all([ReadinessCheck::Description, ReadinessCheck::ProjectLink, ReadinessCheck::TechStack, ReadinessCheck::Tags], $readiness->isComplete(...))],
]);

/**
 * The IDs of the records the query keeps, in ID order.
 *
 * @param  Builder<covariant Model>  $query
 * @return array<mixed>
 */
function contentReadinessQueryIds(Builder $query): array
{
    return $query->orderBy('id')
        ->pluck('id')
        ->all();
}

/**
 * The IDs of the records ContentReadiness gives the verdict for, evaluated in PHP, in ID order.
 *
 * @param  Builder<covariant Model>  $query
 * @param  Closure(ContentReadiness): bool  $verdict
 * @return array<mixed>
 */
function contentReadinessVerdictIds(Builder $query, Closure $verdict): array
{
    $relations = match (true) {
        $query->getModel() instanceof Video => [],
        $query->getModel() instanceof Episode => ['seo', 'podcast'],
        default => ['seo'],
    };

    return $query->with($relations)
        ->orderBy('id')
        ->get()
        ->filter(fn (Model $record): bool => match (true) {
            $record instanceof Post,
            $record instanceof Project,
            $record instanceof Podcast,
            $record instanceof Episode,
            $record instanceof NewsletterIssue,
            $record instanceof Video => $verdict(new ContentReadiness($record)),
            default => throw new UnexpectedValueException('Unsupported readiness record.'),
        })
        ->values()
        ->modelKeys();
}

/**
 * A varied set of records for every content type, covering each readiness check's edge cases:
 * blank and whitespace values, bundled post artwork, SEO descriptions set only on the SEO row,
 * malformed Transistor links and tech stacks with blank or non-string entries.
 */
function createContentReadinessAgreementFixtures(): void
{
    $tag = Tag::factory()->create();
    $seoOnlyDescription = ['description' => 'Set on the SEO row only.'];

    Post::factory()->create();
    Post::factory()->create(['slug' => 'from-kansas-to-florida-a-developers-journey']);
    Post::factory()
        ->published()
        ->create(['slug' => 'hello-world-why-im-starting-this-blog', 'excerpt' => null]);
    Post::factory()
        ->scheduled()
        ->create(['excerpt' => " \t\n", 'featured_image_path' => 'posts/whitespace-excerpt.webp']);
    Post::factory()
        ->inReview()
        ->create(['content' => " \n", 'category_id' => null, 'featured_image_path' => ' ']);
    Post::factory()
        ->published()
        ->create(['featured_image_path' => 'posts/ready.webp'])
        ->attachTag($tag);
    Post::factory()
        ->create(['excerpt' => null, 'featured_image_path' => 'posts/seo-only.webp'])
        ->seo()
        ->update($seoOnlyDescription);

    Project::factory()->create();
    Project::factory()
        ->published()
        ->create(['featured_image_path' => 'projects/ready.webp', 'url' => 'https://example.com', 'tech_stack' => ['Laravel']])
        ->attachTag($tag);
    Project::factory()->create(['github_url' => 'https://github.com/example/repo', 'tech_stack' => ['', " \t", 1]]);
    Project::factory()->create(['description' => " \n", 'content' => null, 'tech_stack' => ['  Filament  ']]);
    Project::factory()
        ->featured()
        ->create(['url' => "\t", 'featured_image_path' => '', 'tech_stack' => []]);

    Podcast::factory()->create();
    Podcast::factory()
        ->inactive()
        ->create();
    Podcast::factory()->create(['long_description' => 'About.', 'cover_image_path' => 'podcasts/ready.webp', 'rss_url' => 'https://example.com/feed.xml']);
    Podcast::factory()->create(['spotify_url' => '  ', 'youtube_url' => 'https://youtube.com/@example']);
    Podcast::factory()
        ->inactive()
        ->create(['description' => '', 'apple_url' => 'https://podcasts.apple.com/example'])
        ->seo()
        ->update($seoOnlyDescription);

    $podcast = Podcast::factory()->create();
    $notes = ['show_notes' => 'Notes.'];

    Episode::factory()
        ->for($podcast)
        ->create();
    Episode::factory()
        ->for($podcast)
        ->published()
        ->create(['transistor_url' => 'https://share.transistor.fm/s/abc123/', 'featured_image_path' => 'episodes/ready.webp', ...$notes])
        ->attachTag($tag);
    Episode::factory()
        ->for($podcast)
        ->scheduled()
        ->create(['transistor_url' => 'https://share.transistor.fm/s/abc123//', ...$notes]);
    Episode::factory()
        ->for($podcast)
        ->create(['transistor_url' => 'https://share.transistor.fm/s/abc-123', 'show_notes' => " \t"]);

    foreach ([
        'https://share.transistor.fm/s/',
        "https://share.transistor.fm/s/abc123\n",
        'https://share.transistor.fm/s/abc123?autoplay=1',
        'HTTPS://SHARE.TRANSISTOR.FM/S/ABC123',
        'http://share.transistor.fm/s/abc123',
    ] as $transistorUrl) {
        Episode::factory()
            ->for($podcast)
            ->create(['transistor_url' => $transistorUrl, ...$notes]);
    }

    Episode::factory()
        ->for($podcast)
        ->create(['transistor_url' => 'https://example.com/s/428dcd6b', 'youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk', ...$notes]);
    Episode::factory()->create(['podcast_id' => null, 'transistor_url' => null, 'description' => ' ']);

    NewsletterIssue::factory()->create();
    NewsletterIssue::factory()
        ->published()
        ->create(['excerpt' => 'Summary.']);
    NewsletterIssue::factory()
        ->sent()
        ->create(['content' => "\n"])
        ->seo()
        ->update($seoOnlyDescription);
    NewsletterIssue::factory()
        ->scheduled()
        ->create(['excerpt' => '  ']);

    $completeVideo = ['description' => 'Description.', 'thumbnail_url' => 'https://example.com/thumb.jpg', 'duration' => '10:00', 'synced_at' => now()];

    Video::factory()->create();
    Video::factory()->create($completeVideo);
    Video::factory()->create([...$completeVideo, 'duration' => ' ']);
    Video::factory()->create([...$completeVideo, 'duration' => '0', 'synced_at' => null]);
}
