<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Presenters\Concerns\LinksToPublicPageOrPreview;
use App\Presenters\EpisodePresenter;
use App\Presenters\NewsletterIssuePresenter;
use App\Presenters\PostPresenter;
use App\Presenters\ProjectPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\freezeSecond;

covers(LinksToPublicPageOrPreview::class, PostPresenter::class, ProjectPresenter::class, EpisodePresenter::class, NewsletterIssuePresenter::class);

pest()->use(RefreshDatabase::class);

function contentPresenter(Post|Project|Episode|NewsletterIssue $content): PostPresenter|ProjectPresenter|EpisodePresenter|NewsletterIssuePresenter
{
    return match (true) {
        $content instanceof Post => PostPresenter::from($content),
        $content instanceof Project => ProjectPresenter::from($content),
        $content instanceof Episode => EpisodePresenter::from($content),
        $content instanceof NewsletterIssue => NewsletterIssuePresenter::from($content),
    };
}

it('returns the public page of live content', function (string $type, string $path) {
    $content = PublishableFixtures::ready($type);
    $content->publish();

    $url = contentPresenter($content)->publicUrl();

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

    $url = contentPresenter($content)->publicUrl();

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

    $url = contentPresenter($episode)->publicUrl();

    expect($url)
        ->toBeNull();
});

it('signs a preview link that expires after two hours', function (string $type, string $routeName, string $parameter) {
    freezeSecond();
    $content = PublishableFixtures::ready($type);

    $url = contentPresenter($content)->previewUrl();

    expect($url)
        ->toBe(URL::temporarySignedRoute($routeName, now()->addHours(2), [$parameter => $content]));
})->with([
    'post' => ['post', 'preview.post', 'post'],
    'project' => ['project', 'preview.project', 'project'],
    'episode' => ['episode', 'preview.episode', 'episode'],
    'newsletter issue' => ['newsletter issue', 'preview.newsletterIssue', 'newsletterIssue'],
]);

it('falls back to a signed preview when there is no public page', function () {
    freezeSecond();
    $project = PublishableFixtures::ready('project');
    $presenter = contentPresenter($project);

    $url = $presenter->publicOrPreviewUrl();

    expect($url)
        ->toBe($presenter->previewUrl());
});
