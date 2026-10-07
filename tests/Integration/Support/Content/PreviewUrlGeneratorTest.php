<?php

use App\Models\Podcast;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\freezeSecond;

covers(PreviewUrlGenerator::class);

pest()->use(RefreshDatabase::class);

it('returns the public page of live content', function (string $type, string $path) {
    $content = PublishableFixtures::ready($type);
    $content->publish();

    $url = app(PreviewUrlGenerator::class)->publicUrl($content);

    expect($url)
        ->toBe(url($path));
})->with([
    'post' => ['post', '/blog/ready-post'],
    'project' => ['project', '/projects/ready-project'],
    'episode' => ['episode', '/podcasts/show/ready-episode'],
    'newsletter issue' => ['newsletter issue', '/newsletter/ready-issue'],
]);

it('has no public page for content that is not live yet', function (string $type, ?int $days) {
    $content = PublishableFixtures::ready($type, ['published_at' => $days === null ? null : now()->addDays($days)]);

    if ($days !== null) {
        $content->publish();
    }

    $url = app(PreviewUrlGenerator::class)->publicUrl($content);

    expect($url)
        ->toBeNull();
})->with([
    'draft post' => ['post', null],
    'scheduled newsletter issue' => ['newsletter issue', 1],
]);

it('has no public page for a live episode of an inactive show', function () {
    $episode = PublishableFixtures::ready('episode', [
        'podcast_id' => Podcast::factory()
            ->inactive()
            ->create()
            ->id,
    ]);
    $episode->publish();

    $url = app(PreviewUrlGenerator::class)->publicUrl($episode);

    expect($url)
        ->toBeNull();
});

it('falls back to a signed preview when there is no public page', function () {
    freezeSecond();
    $project = PublishableFixtures::ready('project');
    $generator = app(PreviewUrlGenerator::class);

    $url = $generator->publicOrPreviewUrl($project);

    expect($url)
        ->toBe($generator->for($project));
});
