<?php

use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('creates a newsletter issue through the Filament form', function () {
    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'Building Better Boundaries',
            'slug' => 'building-better-boundaries',
            'excerpt' => 'A practical note about application boundaries.',
            'content' => 'Issue content.',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(NewsletterIssue::query()->sole())
        ->title->toBe('Building Better Boundaries')
        ->content->toBe('Issue content.')
        ->status->toBe(PublishStatus::Draft);
});

it('updates a newsletter issue through the Filament form', function () {
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm([
            'title' => 'Updated issue',
            'slug' => 'updated-issue',
            'content' => 'Updated content.',
            'status' => PublishStatus::InReview,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh())
        ->title->toBe('Updated issue')
        ->content->toBe('Updated content.')
        ->status->toBe(PublishStatus::InReview);
});

it('rejects slugs reserved by static newsletter routes', function (string $slug) {
    livewire(CreateNewsletterIssue::class)
        ->fillForm([
            'title' => 'Reserved slug',
            'slug' => $slug,
            'content' => 'Issue content.',
            'status' => PublishStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);

    expect(NewsletterIssue::query()->exists())
        ->toBeFalse();
})->with(['confirmed', 'rss']);

it('rejects a reserved slug when updating a newsletter issue', function (string $slug) {
    $issue = NewsletterIssue::factory()->create(['slug' => 'original-issue']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['slug' => $slug])
        ->call('save')
        ->assertHasFormErrors(['slug']);

    expect($issue->refresh())
        ->slug->toBe('original-issue');
})->with(['confirmed', 'rss']);

it('locks the publish date of a sent issue so delivered links keep working', function () {
    $publishedAt = now()
        ->subDay()
        ->startOfMinute();
    $issue = NewsletterIssue::factory()
        ->sent()
        ->create(['published_at' => $publishedAt]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertFormFieldDisabled('published_at')
        ->fillForm(['published_at' => now()->addWeek()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh())
        ->published_at->toEqual($publishedAt);
});

it('keeps the publish date editable on an issue that has not been sent', function () {
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertFormFieldEnabled('published_at');
});
