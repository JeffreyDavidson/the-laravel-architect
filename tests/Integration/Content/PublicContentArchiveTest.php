<?php

use App\Content\PublicContentArchive;
use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
        'status' => ProjectStatus::Published,
    ]);
    Project::query()->create([
        'title' => 'Draft project',
        'slug' => 'draft-project',
        'description' => 'Not public',
        'status' => ProjectStatus::Draft,
    ]);

    $archive = app(PublicContentArchive::class)->export();
    $encoded = json_encode($archive, JSON_THROW_ON_ERROR);

    expect($archive['posts'])->toHaveCount(1)
        ->and($archive['posts'][0]['slug'])->toBe('published-post')
        ->and($archive['posts'][0]['category_slug'])->toBe('architecture')
        ->and($archive['posts'][0]['tags'][0]['name'])->toBe('Laravel')
        ->and($archive['posts'][0]['seo']['canonical_url'])->toBe('https://thelaravelarchitect.com/blog/published-post')
        ->and($archive['projects'])->toHaveCount(1)
        ->and($archive['projects'][0]['slug'])->toBe('published-project')
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

    $post = Post::query()->where('slug', 'production-post')->firstOrFail();
    $podcast = Podcast::query()->where('slug', 'production-podcast')->firstOrFail();
    $stagingAuthor = User::query()->where('email', 'staging-content@example.test')->firstOrFail();

    expect($counts['posts'])->toBe(1)
        ->and($post->status)->toBe(PublishStatus::Published)
        ->and($post->category?->slug)->toBe('architecture')
        ->and($post->tags->pluck('name')->all())->toBe(['Laravel'])
        ->and($post->seo->canonical_url)->toBe('https://thelaravelarchitect.com/blog/production-post')
        ->and($post->user_id)->toBe($stagingAuthor->getKey())
        ->and($stagingAuthor->is_admin)->toBeFalse()
        ->and($podcast->is_active)->toBeTrue()
        ->and($localDraft->fresh()?->status)->toBe(PublishStatus::Draft)
        ->and($stale->fresh()?->status)->toBe(PublishStatus::Draft)
        ->and($stale->fresh()?->published_at)->toBeNull();
});

test('unsafe referenced media paths are rejected', function (): void {
    $archive = publicContentArchiveFixture();
    $archive['posts'][0]['featured_image_path'] = '../private/file.webp';

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
        'projects' => [],
        'podcasts' => [[
            'name' => 'Production podcast',
            'slug' => 'production-podcast',
            'description' => 'Production podcast description',
            'sort_order' => 1,
            'seo' => null,
        ]],
        'episodes' => [],
        'videos' => [],
        'testimonials' => [],
    ];
}
