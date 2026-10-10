<?php

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\CreateEpisode;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\CreatePost;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('offers only pre-publication statuses when creating content', function (string $page, array $statuses) {
    livewire($page)
        ->assertFormFieldExists('status', fn (Select $field): bool => array_keys($field->getOptions()) === $statuses);
})->with([
    'post' => [CreatePost::class, ['draft', 'in_review']],
    'newsletter issue' => [CreateNewsletterIssue::class, ['draft', 'in_review']],
    'project' => [CreateProject::class, ['draft']],
    'episode' => [CreateEpisode::class, ['draft']],
]);

it('rejects a published status submitted through the form', function () {
    $post = PublishableFixtures::ready('post');

    livewire(PublishableFixtures::editPage('post'), ['record' => $post->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Published])
        ->call('save')
        ->assertHasFormErrors(['status']);

    $post->refresh();

    expect($post->getAttribute('status'))
        ->toBe(PublishStatus::Draft);
});

it('locks the status of live and scheduled content while the rest of the form still saves', function (string $type, int $days, PublishStatus $status) {
    $record = PublishableFixtures::ready($type, ['published_at' => now()->addDays($days)]);
    $record->publish();

    livewire(PublishableFixtures::editPage($type), ['record' => $record->getRouteKey()])
        ->assertFormFieldDisabled('status')
        ->assertSchemaStateSet(['status' => $status->value])
        ->fillForm(['title' => 'Edited title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->fresh())
        ->title->toBe('Edited title')
        ->status->toBe($status);
})->with([
    'published post' => ['post', -1, PublishStatus::Published],
    'scheduled newsletter issue' => ['newsletter issue', 1, PublishStatus::Scheduled],
]);

it('never changes a locked status from a forged form value', function () {
    $post = PublishableFixtures::ready('post');
    $post->publish();

    livewire(PublishableFixtures::editPage('post'), ['record' => $post->getRouteKey()])
        ->fillForm(['status' => PublishStatus::Draft])
        ->call('save');

    $post->refresh();

    expect($post->getAttribute('status'))
        ->toBe(PublishStatus::Published);
});
