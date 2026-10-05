<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);
});

it('renders the newsletter issue create page for an authorized user', function () {
    $this->get(NewsletterIssueResource::getUrl('create'))
        ->assertOk();
});

it('renders the newsletter issue edit page for an authorized user', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Newsletter issue page coverage',
        'slug' => 'newsletter-issue-page-coverage',
        'content' => 'Newsletter issue content',
        'status' => PublishStatus::Draft,
    ]);

    $this->get(NewsletterIssueResource::getUrl('edit', ['record' => $issue]))
        ->assertOk();
});
