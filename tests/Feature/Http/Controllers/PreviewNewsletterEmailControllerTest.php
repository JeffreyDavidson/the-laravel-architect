<?php

use App\Enums\NewsletterTemplate;
use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('lets an authorized editor preview a draft email without sending it', function () {
    Mail::fake();
    actingAs(User::factory()->create(['is_admin' => true]));
    $issue = NewsletterIssue::factory()->create(['email_template' => NewsletterTemplate::RelatedReading]);

    get(route('preview.newsletterEmail', $issue))
        ->assertOk()
        ->assertSee('From Jeffrey Davidson')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Cache-Control', 'no-store, private');

    Mail::assertNothingSent();
    expect($issue->refresh())
        ->sent_at->toBeNull();
});

it('refuses an email preview to a non-administrator', function () {
    actingAs(User::factory()->create(['is_admin' => false]));
    $issue = NewsletterIssue::factory()->create();

    get(route('preview.newsletterEmail', $issue))
        ->assertForbidden();
});

it('refuses an email preview to an anonymous reader', function () {
    $issue = NewsletterIssue::factory()->create();

    get(route('preview.newsletterEmail', $issue))
        ->assertRedirect();
});
