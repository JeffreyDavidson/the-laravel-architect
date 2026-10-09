<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use App\Presenters\EpisodePresenter;
use App\Presenters\NewsletterIssuePresenter;
use App\Presenters\PostPresenter;
use App\Presenters\ProjectPresenter;
use App\Services\ContentUrls;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;

covers(ContentUrls::class);

pest()->use(RefreshDatabase::class);

it('links each content type to its presenter\'s public or preview page', function () {
    $post = Post::factory()->published()
        ->create();
    $project = Project::factory()->create();
    $episode = Episode::factory()->published()
        ->create();
    $issue = NewsletterIssue::factory()->published()
        ->create();
    $urls = app(ContentUrlsContract::class);

    expect($urls->publicOrPreviewUrl($post))
        ->toBe(PostPresenter::from($post)->publicOrPreviewUrl())
        ->and($urls->publicOrPreviewUrl($project))
        ->toBe(ProjectPresenter::from($project)->publicOrPreviewUrl())
        ->and($urls->publicOrPreviewUrl($episode))
        ->toBe(EpisodePresenter::from($episode)->publicOrPreviewUrl())
        ->and($urls->publicOrPreviewUrl($issue))
        ->toBe(NewsletterIssuePresenter::from($issue)->publicOrPreviewUrl());
});

it('refuses content it has no page for', function () {
    app(ContentUrlsContract::class)->publicOrPreviewUrl(new Video);
})->throws(LogicException::class);
