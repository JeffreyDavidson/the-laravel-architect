<?php

use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the newsletter issue create page for an authorized user', function () {
    get(NewsletterIssueResource::getUrl('create'))
        ->assertOk();
});

it('renders the newsletter issue edit page for an authorized user', function () {
    $issue = NewsletterIssue::factory()->create();

    get(NewsletterIssueResource::getUrl('edit', ['record' => $issue]))
        ->assertOk();
});
