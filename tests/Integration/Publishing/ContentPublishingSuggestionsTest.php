<?php

use App\Models\Post;
use App\Models\Tag;
use App\Publishing\ContentPublishingSuggestions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\PublishingSuggestions;
use Tests\Support\PublishableFixtures;

covers(ContentPublishingSuggestions::class);

pest()->use(RefreshDatabase::class);

it('suggests the missing details that do not block publishing', function (string $type, array $suggestions) {
    $record = PublishableFixtures::ready($type);

    expect(app(PublishingSuggestions::class)->suggestions($record))->toBe($suggestions);
})->with([
    'post' => ['post', ['Featured image', 'Tags']],
    'episode' => ['episode', ['Show notes', 'Featured image', 'Tags']],
]);

it('leaves out the details that block publishing', function () {
    $post = Post::factory()->create(['excerpt' => null, 'category_id' => null]);

    expect(app(PublishingSuggestions::class)->suggestions($post))
        ->not->toContain('Excerpt')
        ->not->toContain('Category')
        ->toContain('Tags');
});

it('suggests nothing once every detail is complete', function () {
    $post = PublishableFixtures::readyPost(['featured_image_path' => 'posts/complete.webp']);
    $post->attachTag(Tag::factory()->create());

    expect(app(PublishingSuggestions::class)->suggestions($post->refresh()))->toBeEmpty();
});
