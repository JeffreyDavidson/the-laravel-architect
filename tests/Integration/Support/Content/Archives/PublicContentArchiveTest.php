<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\User;
use App\Support\Content\Archives\PublicContentArchiveExporter;
use App\Support\Content\Archives\PublicContentArchiveImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

test('public archives exclude private project repository URLs', function () {
    Project::query()->create([
        'title' => 'Public case study', 'slug' => 'public-case-study', 'description' => 'Description',
        'status' => PublishStatus::Published, 'github_url' => 'https://github.com/example/confidential-project',
    ]);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect(json_encode($archive, JSON_THROW_ON_ERROR))->not->toContain('confidential-project');
});

/**
 * @return list<array<array-key, mixed>>
 */
function publicArchiveRecords(mixed $value): array
{
    if (! is_array($value)) {
        throw new RuntimeException('The archive records were not an array.');
    }

    $records = [];

    foreach ($value as $record) {
        if (! is_array($record)) {
            throw new RuntimeException('The archive contained an invalid record.');
        }

        $records[] = $record;
    }

    return $records;
}

/** @return list<string> */
function publicArchiveSlugs(mixed $value): array
{
    $records = publicArchiveRecords($value);
    $slugs = [];

    foreach ($records as $record) {
        if (! is_string($record['slug'] ?? null)) {
            throw new RuntimeException('The archive record did not contain a slug.');
        }

        $slugs[] = $record['slug'];
    }

    return $slugs;
}

/**
 * @return array{slug: string, category_slug: string, tags: list<array{name: string}>, seo: array{canonical_url: string}}
 */
function publicArchivePost(mixed $value): array
{
    if (! is_array($value) || ! is_string($value['slug'] ?? null) || ! is_string($value['category_slug'] ?? null)) {
        throw new RuntimeException('The archive post was invalid.');
    }

    $tags = publicArchiveRecords($value['tags'] ?? null);
    $normalizedTags = [];

    foreach ($tags as $tag) {
        if (! is_string($tag['name'] ?? null)) {
            throw new RuntimeException('The archive post tag was invalid.');
        }

        $normalizedTags[] = ['name' => $tag['name']];
    }

    $seo = $value['seo'] ?? null;

    if (! is_array($seo) || ! is_string($seo['canonical_url'] ?? null)) {
        throw new RuntimeException('The archive post SEO data was invalid.');
    }

    return [
        'slug' => $value['slug'],
        'category_slug' => $value['category_slug'],
        'tags' => $normalizedTags,
        'seo' => ['canonical_url' => $seo['canonical_url']],
    ];
}

/**
 * @return array{slug: string, tech_stack: list<string>}
 */
function publicArchiveProject(mixed $value): array
{
    if (! is_array($value) || ! is_string($value['slug'] ?? null) || ! is_array($value['tech_stack'] ?? null)) {
        throw new RuntimeException('The archive project was invalid.');
    }

    $techStack = [];

    foreach ($value['tech_stack'] as $technology) {
        if (! is_string($technology)) {
            throw new RuntimeException('The archive project technology was invalid.');
        }

        $techStack[] = $technology;
    }

    return ['slug' => $value['slug'], 'tech_stack' => $techStack];
}

test('exports every public record exactly once when lazy chunk sort values tie', function (): void {
    $this->travelTo('2026-09-01 12:00:00');

    $recordCount = 101;
    $timestamp = now()->subDay()
        ->toDateTimeString();
    $author = User::factory()->create();

    $categories = [];

    foreach (range(1, $recordCount) as $index) {
        $categories[] = [
            'name' => 'Tied category',
            'slug' => "tied-category-{$index}",
            'description' => 'A public category.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    DB::table('categories')->insert($categories);
    $categoryIds = Category::query()->orderBy('id')
        ->pluck('id')
        ->all();

    $posts = [];
    $projects = [];

    foreach (range(1, $recordCount) as $index) {
        $posts[] = [
            'title' => 'Tied post',
            'slug' => "tied-post-{$index}",
            'content' => 'Public content.',
            'category_id' => $categoryIds[$index - 1],
            'user_id' => $author->getKey(),
            'status' => PublishStatus::Published->value,
            'published_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
        $projects[] = [
            'title' => 'Tied project',
            'slug' => "tied-project-{$index}",
            'description' => 'A public project.',
            'sort_order' => 0,
            'status' => PublishStatus::Published->value,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    DB::table('posts')->insert($posts);
    DB::table('projects')->insert($projects);

    $podcasts = [];

    foreach (range(1, $recordCount) as $index) {
        $podcasts[] = [
            'name' => 'Tied podcast',
            'slug' => "tied-podcast-{$index}",
            'description' => 'A public podcast.',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    DB::table('podcasts')->insert($podcasts);
    $podcastId = Podcast::query()->orderBy('id')
        ->value('id');

    $episodes = [];
    $videos = [];

    foreach (range(1, $recordCount) as $index) {
        $episodes[] = [
            'podcast_id' => $podcastId,
            'title' => 'Tied episode',
            'slug' => "tied-episode-{$index}",
            'description' => 'A public episode.',
            'status' => PublishStatus::Published->value,
            'published_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
        $videos[] = [
            'youtube_id' => "tied-video-{$index}",
            'title' => 'Tied video',
            'slug' => "tied-video-{$index}",
            'description' => 'A public video.',
            'published_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }

    DB::table('episodes')->insert($episodes);
    DB::table('videos')->insert($videos);

    $archive = app(PublicContentArchiveExporter::class)->export();

    foreach ([
        'categories' => 'tied-category',
        'posts' => 'tied-post',
        'projects' => 'tied-project',
        'podcasts' => 'tied-podcast',
        'episodes' => 'tied-episode',
        'videos' => 'tied-video',
    ] as $type => $prefix) {
        $slugs = publicArchiveSlugs($archive[$type] ?? null);
        sort($slugs);
        $expectedSlugs = array_map(
            fn (int $index): string => "{$prefix}-{$index}",
            range(1, $recordCount),
        );
        sort($expectedSlugs);

        expect($slugs)->toHaveCount($recordCount)
            ->and($slugs)
            ->toBe($expectedSlugs);
    }
});

test('export query count stays bounded as tagged content grows', function (): void {
    $project = Project::query()->create([
        'title' => 'First project',
        'description' => 'A public project.',
        'status' => PublishStatus::Published,
    ]);
    $project->syncTags(['Laravel']);

    DB::enableQueryLog();
    app(PublicContentArchiveExporter::class)->export();
    $initialQueryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    foreach (range(1, 9) as $index) {
        $project = Project::query()->create([
            'title' => "Additional project {$index}",
            'description' => 'Another public project.',
            'status' => PublishStatus::Published,
        ]);
        $project->syncTags(['Laravel']);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();
    $archive = app(PublicContentArchiveExporter::class)->export();
    $expandedQueryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($archive['projects'])->toHaveCount(10)
        ->and($expandedQueryCount)
        ->toBe($initialQueryCount);
});

it('exports and synchronizes published newsletter issues', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Production newsletter issue',
        'slug' => 'production-newsletter-issue',
        'excerpt' => 'A public issue.',
        'content' => 'Issue content.',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $archive = app(PublicContentArchiveExporter::class)->export();
    $issues = publicArchiveRecords($archive['newsletter_issues'] ?? null);

    expect($issues)->toHaveCount(1)
        ->and($issues[0]['slug'])
        ->toBe($issue->slug)
        ->and($issues[0]['content'])
        ->toBe('Issue content.');

    NewsletterIssue::query()->delete();

    $counts = app(PublicContentArchiveImporter::class)->sync($archive);

    expect($counts['newsletter_issues'])->toBe(1)
        ->and(NewsletterIssue::query()->sole()
            ->title)
        ->toBe('Production newsletter issue');
});

test('only public content and its presentation data are exported', function (): void {
    $author = User::factory()->create([
        'email' => 'private-author@example.test',
        'password' => 'private-password',
    ]);
    Subscriber::query()->create([
        'email' => 'private-subscriber@example.test',
        'subscribed_at' => now(),
    ]);
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $post = Post::query()->create([
        'title' => 'Published post',
        'slug' => 'published-post',
        'content' => 'Public content',
        'featured_image_path' => 'posts/published.webp',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
        'review_notes' => 'Private editorial note',
        'reviewed_by' => $author->getKey(),
    ]);
    $post->syncTags(['Laravel']);
    $post->seo()
        ->update(['canonical_url' => 'https://thelaravelarchitect.com/blog/published-post']);
    Post::query()->create([
        'title' => 'Draft post',
        'slug' => 'draft-post',
        'content' => 'Not public',
        'category_id' => $category->getKey(),
        'user_id' => $author->getKey(),
        'status' => PublishStatus::Draft,
    ]);
    Project::query()->create([
        'title' => 'Published project',
        'slug' => 'published-project',
        'description' => 'Public project',
        'tech_stack' => ['Laravel', 'Pest'],
        'status' => PublishStatus::Published,
    ]);
    Project::query()->create([
        'title' => 'Draft project',
        'slug' => 'draft-project',
        'description' => 'Not public',
        'status' => PublishStatus::Draft,
    ]);

    $archive = app(PublicContentArchiveExporter::class)->export();
    $encoded = json_encode($archive, JSON_THROW_ON_ERROR);

    $post = publicArchivePost(publicArchiveRecords($archive['posts'] ?? null)[0] ?? null);
    $project = publicArchiveProject(publicArchiveRecords($archive['projects'] ?? null)[0] ?? null);

    expect($archive['posts'])->toHaveCount(1)
        ->and($post['slug'])
        ->toBe('published-post')
        ->and($post['category_slug'])
        ->toBe('architecture')
        ->and($post['tags'][0]['name'])
        ->toBe('Laravel')
        ->and($post['seo']['canonical_url'])
        ->toBe('https://thelaravelarchitect.com/blog/published-post')
        ->and($archive['projects'])
        ->toHaveCount(1)
        ->and($project['slug'])
        ->toBe('published-project')
        ->and($project['tech_stack'])
        ->toBe(['Laravel', 'Pest'])
        ->and($archive['categories'])
        ->toHaveCount(1)
        ->and($encoded)
        ->not->toContain('private-author@example.test')
        ->and($encoded)
        ->not->toContain('private-subscriber@example.test')
        ->and($encoded)
        ->not->toContain('private-password')
        ->and($encoded)
        ->not->toContain('Private editorial note');
});

test('public content is synchronized without importing production identities', function (): void {
    config()->set('content-sync.staging_author.email', 'staging-content@example.test');
    config()->set('content-sync.staging_author.name', 'Staging Content');
    $localAuthor = User::factory()->create();
    $localDraft = Post::query()->create([
        'title' => 'Local draft',
        'slug' => 'local-draft',
        'content' => 'Keep me',
        'user_id' => $localAuthor->getKey(),
        'status' => PublishStatus::Draft,
    ]);
    $stale = Post::query()->create([
        'title' => 'Stale public post',
        'slug' => 'stale-public-post',
        'content' => 'Unpublish me',
        'user_id' => $localAuthor->getKey(),
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $counts = app(PublicContentArchiveImporter::class)->sync(publicContentArchiveFixture());

    $post = Post::query()->where('slug', 'production-post')
        ->sole();
    $project = Project::query()->where('slug', 'production-project')
        ->firstOrFail();
    $podcast = Podcast::query()->where('slug', 'production-podcast')
        ->firstOrFail();
    $stagingAuthor = User::query()->where('email', 'staging-content@example.test')
        ->firstOrFail();
    $seo = $post->seo()
        ->firstOrFail();

    expect($counts['posts'])->toBe(1)
        ->and($post->status)
        ->toBe(PublishStatus::Published)
        ->and($post->category?->slug)
        ->toBe('architecture')
        ->and($post->tags->pluck('name')
            ->all())
        ->toBe(['Laravel'])
        ->and($seo->getAttribute('canonical_url'))
        ->toBe('https://thelaravelarchitect.com/blog/production-post')
        ->and($post->user_id)
        ->toBe($stagingAuthor->getKey())
        ->and($stagingAuthor->is_admin)
        ->toBeFalse()
        ->and($project->tech_stack)
        ->toBe(['Laravel', 'Pest'])
        ->and($podcast->is_active)
        ->toBeTrue()
        ->and($localDraft->fresh()
            ?->status)
        ->toBe(PublishStatus::Draft)
        ->and($stale->fresh()
            ?->status)
        ->toBe(PublishStatus::Draft)
        ->and($stale->fresh()
            ?->published_at)
        ->toBeNull();
});

test('unsafe referenced media paths are rejected', function (): void {
    $archive = publicContentArchiveFixture();
    $posts = publicArchiveRecords($archive['posts'] ?? null);
    $posts[0]['featured_image_path'] = '../private/file.webp';
    $archive['posts'] = $posts;

    expect(fn () => app(PublicContentArchiveImporter::class)->mediaPaths($archive))
        ->toThrow(InvalidArgumentException::class, 'unsafe media path');
});

/** @return array<string, mixed> */
function publicContentArchiveFixture(): array
{
    return [
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'categories' => [[
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Architecture articles',
        ]],
        'posts' => [[
            'title' => 'Production post',
            'slug' => 'production-post',
            'excerpt' => 'Production excerpt',
            'content' => 'Production content',
            'featured_image_path' => 'posts/production.webp',
            'published_at' => now()->subDay()
                ->toAtomString(),
            'category_slug' => 'architecture',
            'tags' => [['name' => 'Laravel', 'type' => null]],
            'seo' => [
                'canonical_url' => 'https://thelaravelarchitect.com/blog/production-post',
            ],
        ]],
        'projects' => [[
            'title' => 'Production project',
            'slug' => 'production-project',
            'description' => 'Production project description',
            'tech_stack' => ['Laravel', 'Pest'],
            'is_featured' => true,
            'sort_order' => 1,
            'tags' => [],
            'seo' => null,
        ]],
        'podcasts' => [[
            'name' => 'Production podcast',
            'slug' => 'production-podcast',
            'description' => 'Production podcast description',
            'sort_order' => 1,
            'seo' => null,
        ]],
        'episodes' => [],
        'videos' => [],
    ];
}
