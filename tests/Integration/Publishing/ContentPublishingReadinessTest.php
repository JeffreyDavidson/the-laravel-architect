<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Actions\PublishContent;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Exceptions\ContentNotReadyToPublish;
use Tests\Support\PublishableFixtures;

pest()->use(RefreshDatabase::class);

dataset('publishable types', ['post', 'project', 'episode', 'newsletter issue']);

it('publishes content whose required details are present', function (string $type) {
    $record = PublishableFixtures::ready($type);

    app(PublishContent::class)
        ->handle($record);

    $record->refresh();
    expect($record->isPublished())
        ->toBeTrue();
})->with('publishable types');

it('publishes content that is missing only advisory details', function () {
    $post = PublishableFixtures::ready('post', ['featured_image_path' => null]);

    app(PublishContent::class)
        ->handle($post);

    $post->refresh();
    expect($post->getAttribute('status'))
        ->toBe(PublishStatus::Published);
});

it('refuses to publish content that is missing required details and lists them', function (string $type, array $issues, string $message) {
    $record = PublishableFixtures::ready($type, PublishableFixtures::withoutRequiredDetails($type));

    $exception = null;

    try {
        app(PublishContent::class)
            ->handle($record);
    } catch (ContentNotReadyToPublish $caught) {
        $exception = $caught;
    }

    $record->refresh();
    expect($exception?->issues)
        ->toBe($issues)
        ->and($exception?->getMessage())
        ->toBe($message)
        ->and($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft);
})->with([
    'post' => ['post', ['Content', 'Excerpt', 'Category'], 'Post is not ready to publish. Missing: Content, Excerpt, Category.'],
    'project' => ['project', ['Description', 'Case study'], 'Project is not ready to publish. Missing: Description, Case study.'],
    'episode' => ['episode', ['Podcast', 'Description', 'Episode media'], 'Episode is not ready to publish. Missing: Podcast, Description, Episode media.'],
    'newsletter issue' => ['newsletter issue', ['Content'], 'Newsletter Issue is not ready to publish. Missing: Content.'],
]);
