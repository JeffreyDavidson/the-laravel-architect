<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\User;
use App\Support\Content\Archives\PublicContentArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

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

test('export query count stays bounded as tagged content grows', function (): void {
    $project = Project::query()->create([
        'title' => 'First project',
        'description' => 'A public project.',
        'status' => PublishStatus::Published,
    ]);
    $project->syncTags(['Laravel']);

    DB::enableQueryLog();
    app(PublicContentArchive::class)->export();
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
    $archive = app(PublicContentArchive::class)->export();
    $expandedQueryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($archive['projects'])->toHaveCount(10)
        ->and($expandedQueryCount)->toBe($initialQueryCount);
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
    $post->seo()->update(['canonical_url' => 'https://thelaravelarchitect.com/blog/published-post']);
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

    $archive = app(PublicContentArchive::class)->export();
    $encoded = json_encode($archive, JSON_THROW_ON_ERROR);

    $post = publicArchivePost(publicArchiveRecords($archive['posts'] ?? null)[0] ?? null);
    $project = publicArchiveProject(publicArchiveRecords($archive['projects'] ?? null)[0] ?? null);

    expect($archive['posts'])->toHaveCount(1)
        ->and($post['slug'])->toBe('published-post')
        ->and($post['category_slug'])->toBe('architecture')
        ->and($post['tags'][0]['name'])->toBe('Laravel')
        ->and($post['seo']['canonical_url'])->toBe('https://thelaravelarchitect.com/blog/published-post')
        ->and($archive['projects'])->toHaveCount(1)
        ->and($project['slug'])->toBe('published-project')
        ->and($project['tech_stack'])->toBe(['Laravel', 'Pest'])
        ->and($archive['categories'])->toHaveCount(1)
        ->and($encoded)->not->toContain('private-author@example.test')
        ->and($encoded)->not->toContain('private-subscriber@example.test')
        ->and($encoded)->not->toContain('private-password')
        ->and($encoded)->not->toContain('Private editorial note');
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

    $counts = app(PublicContentArchive::class)->sync(publicContentArchiveFixture());

    $post = Post::query()->where('slug', 'production-post')->sole();
    $project = Project::query()->where('slug', 'production-project')->firstOrFail();
    $podcast = Podcast::query()->where('slug', 'production-podcast')->firstOrFail();
    $stagingAuthor = User::query()->where('email', 'staging-content@example.test')->firstOrFail();
    $seo = $post->seo()->firstOrFail();

    expect($counts['posts'])->toBe(1)
        ->and($post->status)->toBe(PublishStatus::Published)
        ->and($post->category?->slug)->toBe('architecture')
        ->and($post->tags->pluck('name')->all())->toBe(['Laravel'])
        ->and($seo->getAttribute('canonical_url'))->toBe('https://thelaravelarchitect.com/blog/production-post')
        ->and($post->user_id)->toBe($stagingAuthor->getKey())
        ->and($stagingAuthor->is_admin)->toBeFalse()
        ->and($project->tech_stack)->toBe(['Laravel', 'Pest'])
        ->and($podcast->is_active)->toBeTrue()
        ->and($localDraft->fresh()?->status)->toBe(PublishStatus::Draft)
        ->and($stale->fresh()?->status)->toBe(PublishStatus::Draft)
        ->and($stale->fresh()?->published_at)->toBeNull();
});

test('unsafe referenced media paths are rejected', function (): void {
    $archive = publicContentArchiveFixture();
    $posts = publicArchiveRecords($archive['posts'] ?? null);
    $posts[0]['featured_image_path'] = '../private/file.webp';
    $archive['posts'] = $posts;

    expect(fn () => app(PublicContentArchive::class)->mediaPaths($archive))
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
            'published_at' => now()->subDay()->toAtomString(),
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
