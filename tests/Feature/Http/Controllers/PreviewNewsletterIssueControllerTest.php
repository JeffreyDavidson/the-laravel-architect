<?php

use App\Models\NewsletterIssue;
use App\Presenters\NewsletterIssuePresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders a signed preview for an unpublished newsletterissue', function () {
    $draft = NewsletterIssue::factory()->create();
    $url = NewsletterIssuePresenter::from($draft)->previewUrl();

    $response = get($url);

    $response->assertOk()
        ->assertSee($draft->title);
});
