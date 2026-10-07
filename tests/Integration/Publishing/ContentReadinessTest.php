<?php

use App\Enums\ContentReadinessStatus;
use App\Enums\ReadinessCheck;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Video;
use App\Publishing\ContentReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

pest()->use(RefreshDatabase::class);

it('counts only a Transistor share link or a YouTube link as episode media', function (array $attributes, bool $complete) {
    /** @var array<string, mixed> $attributes */
    $episode = new Episode($attributes);

    expect(new ContentReadiness($episode)->isComplete(ReadinessCheck::EpisodeMedia))->toBe($complete);
})->with([
    'Transistor share link' => [['transistor_url' => 'https://share.transistor.fm/s/428dcd6b'], true],
    'YouTube link' => [['youtube_url' => 'https://www.youtube.com/watch?v=abcdefghijk'], true],
    'a Transistor URL that is not a share link' => [['transistor_url' => 'https://example.com/s/428dcd6b'], false],
    'nothing' => [[], false],
]);

it('reports actionable missing details for every supported content type', function () {
    $records = [
        Post::factory()->create(),
        Project::factory()->create(),
        Podcast::factory()->create(),
        Episode::factory()->create(),
        NewsletterIssue::factory()->create(),
        Video::factory()->create(),
    ];

    foreach ($records as $record) {
        $readiness = new ContentReadiness($record);

        expect($readiness->isReady())->toBeFalse()
            ->and($readiness->status())
            ->toBe(ContentReadinessStatus::NeedsAttention)
            ->and($readiness->checks())
            ->not->toBeEmpty()
            ->and($readiness->missing())
            ->not->toBeEmpty();
    }
});

it('reports complete public details for each content type that supports readiness checks', function () {
    $tag = Tag::factory()->create();

    $post = Post::factory()->create(['featured_image_path' => 'posts/complete.webp']);
    $post->attachTag($tag);

    $project = Project::factory()->create([
        'featured_image_path' => 'projects/complete.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel'],
    ]);
    $project->attachTag($tag);

    $podcast = Podcast::factory()->create([
        'long_description' => 'About.',
        'cover_image_path' => 'podcasts/complete.webp',
        'rss_url' => 'https://example.com/feed.xml',
    ]);

    $episode = Episode::factory()
        ->for($podcast)
        ->create([
            'show_notes' => 'Show notes.',
            'featured_image_path' => 'episodes/complete.webp',
        ]);
    $episode->attachTag($tag);

    $issue = NewsletterIssue::factory()->create(['excerpt' => 'Summary.']);

    $video = Video::factory()->create([
        'description' => 'Description.',
        'thumbnail_url' => 'https://example.com/thumb.jpg',
        'duration' => '10:00',
        'synced_at' => now(),
    ]);

    foreach ([$post, $project, $podcast, $episode, $issue, $video] as $record) {
        expect(new ContentReadiness($record)->isReady())->toBeTrue();
    }
});

it('reports the missing public project details', function () {
    $project = Project::factory()->create(['content' => null]);

    $readiness = new ContentReadiness($project);

    expect($readiness->isReady())->toBeFalse()
        ->and($readiness->status())
        ->toBe(ContentReadinessStatus::NeedsAttention)
        ->and($readiness->checks())
        ->toHaveCount(6)
        ->and($readiness->missing())
        ->toBe([ReadinessCheck::CaseStudy, ReadinessCheck::FeaturedImage, ReadinessCheck::ProjectLink, ReadinessCheck::TechStack, ReadinessCheck::Tags]);
});

it('reports a project as ready when all public details are present', function () {
    $project = Project::factory()->create([
        'featured_image_path' => 'projects/complete.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel', 'Filament'],
    ]);
    $project->attachTag(Tag::factory()->create());

    $project->load('tags');
    $readiness = new ContentReadiness($project);

    expect($readiness->isReady())->toBeTrue()
        ->and($readiness->status())
        ->toBe(ContentReadinessStatus::Ready)
        ->and($readiness->checks())
        ->toBe([ReadinessCheck::Description, ReadinessCheck::CaseStudy, ReadinessCheck::FeaturedImage, ReadinessCheck::ProjectLink, ReadinessCheck::TechStack, ReadinessCheck::Tags])
        ->and($readiness->missing())
        ->toBeEmpty();
});

it('counts a Transistor episode URL as episode media', function () {
    $episode = new Episode(['transistor_url' => 'https://share.transistor.fm/s/428dcd6b']);

    expect(new ContentReadiness($episode)->isComplete(ReadinessCheck::EpisodeMedia))
        ->toBeTrue();
});

it('counts bundled artwork as a post featured image', function (string $slug, bool $complete) {
    $post = new Post([
        'title' => 'Artwork check',
        'slug' => $slug,
        'content' => 'Content.',
    ]);

    $hasFeaturedImage = new ContentReadiness($post)->isComplete(ReadinessCheck::FeaturedImage);

    expect($hasFeaturedImage)
        ->toBe($complete);
})->with([
    'a post with bundled artwork' => ['hello-world-why-im-starting-this-blog', true],
    'a post without artwork' => ['a-brand-new-post', false],
]);

it('reports no publishing issues when the required details are present', function (string $type) {
    $record = PublishableFixtures::ready($type);

    expect(new ContentReadiness($record)->publishingIssues())
        ->toBeEmpty();
})->with(['post', 'project', 'episode', 'newsletter issue']);

it('lists only the missing required details as publishing issues', function (string $type, array $issues) {
    $record = PublishableFixtures::ready($type, PublishableFixtures::withoutRequiredDetails($type));

    expect(new ContentReadiness($record)->publishingIssues())
        ->toBe($issues);
})->with([
    'post' => ['post', [ReadinessCheck::Content, ReadinessCheck::Excerpt, ReadinessCheck::Category]],
    'project' => ['project', [ReadinessCheck::Description, ReadinessCheck::CaseStudy]],
    'episode' => ['episode', [ReadinessCheck::Podcast, ReadinessCheck::Description, ReadinessCheck::EpisodeMedia]],
    'newsletter issue' => ['newsletter issue', [ReadinessCheck::Content]],
]);

it('does not block publishing on advisory readiness checks', function () {
    $post = PublishableFixtures::ready('post');

    expect($post->getAttribute('featured_image_path'))
        ->toBeNull()
        ->and(new ContentReadiness($post)->publishingIssues())
        ->toBeEmpty();
});
