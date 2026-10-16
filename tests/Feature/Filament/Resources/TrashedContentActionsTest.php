<?php

use App\Filament\Resources\Podcasts\Pages\EditPodcast;
use App\Filament\Resources\Podcasts\Pages\ListPodcasts;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\ListEpisodes;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\ListPosts;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

function trashableRecord(string $type): Post|Project|Episode|NewsletterIssue|Podcast
{
    return $type === 'podcast'
        ? Podcast::factory()->create()
        : PublishableFixtures::ready($type);
}

/** @return array{0: class-string, 1: class-string} */
function trashablePages(string $type): array
{
    return match ($type) {
        'post' => [ListPosts::class, PublishableFixtures::editPage('post')],
        'project' => [ListProjects::class, PublishableFixtures::editPage('project')],
        'episode' => [ListEpisodes::class, PublishableFixtures::editPage('episode')],
        'newsletter issue' => [ListNewsletterIssues::class, PublishableFixtures::editPage('newsletter issue')],
        'podcast' => [ListPodcasts::class, EditPodcast::class],
        default => throw new InvalidArgumentException("Unknown type [{$type}]."),
    };
}

dataset('trashable types', ['post', 'project', 'episode', 'newsletter issue', 'podcast']);

it('moves content to the trash from its edit page', function (string $type) {
    $record = trashableRecord($type);

    livewire(trashablePages($type)[1], ['record' => $record->getRouteKey()])
        ->callAction('delete');

    $record->refresh();

    expect($record->trashed())
        ->toBeTrue();
})->with('trashable types');

it('lists trashed content only when the trashed filter asks for it', function (string $type) {
    $record = trashableRecord($type);
    $record->delete();

    livewire(trashablePages($type)[0])
        ->assertCanNotSeeTableRecords([$record])
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$record]);
})->with('trashable types');

it('restores trashed content from its edit page', function (string $type) {
    // Arrange
    $record = trashableRecord($type);
    $record->delete();

    // Act
    livewire(trashablePages($type)[1], ['record' => $record->getRouteKey()])
        ->callAction('restore');

    // Assert
    $record->refresh();

    expect($record->trashed())
        ->toBeFalse();
})->with('trashable types');

it('force deletes trashed content from its edit page', function (string $type) {
    // Arrange
    $record = trashableRecord($type);
    $record->delete();

    // Act
    livewire(trashablePages($type)[1], ['record' => $record->getRouteKey()])
        ->callAction('forceDelete');

    // Assert
    expect($record::withTrashed()->find($record->getKey()))
        ->toBeNull();
})->with('trashable types');

it('restores trashed content in bulk', function (string $type) {
    // Arrange
    $record = trashableRecord($type);
    $record->delete();

    // Act
    livewire(trashablePages($type)[0])
        ->filterTable('trashed', false)
        ->selectTableRecords([$record])
        ->callAction(TestAction::make(RestoreBulkAction::class)
            ->table()
            ->bulk());

    // Assert
    $record->refresh();

    expect($record->trashed())
        ->toBeFalse();
})->with('trashable types');

it('force deletes trashed content in bulk', function (string $type) {
    // Arrange
    $record = trashableRecord($type);
    $record->delete();

    // Act
    livewire(trashablePages($type)[0])
        ->filterTable('trashed', false)
        ->selectTableRecords([$record])
        ->callAction(TestAction::make(ForceDeleteBulkAction::class)
            ->table()
            ->bulk());

    // Assert
    expect($record::withTrashed()->find($record->getKey()))
        ->toBeNull();
})->with('trashable types');
