<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
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
    $issue = NewsletterIssue::query()->create([
        'title' => 'Original issue',
        'slug' => 'original-issue',
        'content' => 'Original content.',
        'status' => PublishStatus::Draft,
    ]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm([
            'title' => 'Updated issue',
            'slug' => 'updated-issue',
            'content' => 'Updated content.',
            'status' => PublishStatus::Published,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh())
        ->title->toBe('Updated issue')
        ->content->toBe('Updated content.')
        ->status->toBe(PublishStatus::Published);
});
