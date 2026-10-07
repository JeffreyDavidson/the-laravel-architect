<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Publishing\ContentReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('keeps readiness filters consistent with badges for blank project details', function (string $attribute, mixed $value) {
    $project = Project::factory()->create([
        'featured_image_path' => 'projects/ready.webp',
        'url' => 'https://example.test',
        'tech_stack' => ['Laravel'],
        $attribute => $value,
    ]);
    $project->attachTag('Laravel');

    livewire(ListProjects::class)->filterTable('readiness', 'ready')
        ->assertCanNotSeeTableRecords([$project]);
    livewire(ListProjects::class)->filterTable('readiness', 'needs_details')
        ->assertCanSeeTableRecords([$project]);

    expect(new ContentReadiness($project)->isReady())->toBeFalse();
})->with([
    'empty technology' => ['tech_stack', ['']],
    'whitespace technology' => ['tech_stack', ["\t\n"]],
    'non-string technology' => ['tech_stack', [1]],
    'blank description' => ['description', " \t\n"],
    'blank links' => ['url', "\t"],
]);

it('filters projects that are ready to publish', function () {
    $ready = Project::factory()->create([
        'featured_image_path' => 'projects/ready.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel'],
    ]);
    $ready->attachTag(Tag::factory()->create(['name' => 'Laravel']));
    $incomplete = Project::factory()->create();

    livewire(ListProjects::class)
        ->filterTable('readiness', 'ready')
        ->assertCanSeeTableRecords([$ready])
        ->assertCanNotSeeTableRecords([$incomplete]);
});

it('filters projects missing a case study', function () {
    $missingStory = Project::factory()->create(['content' => null]);
    $complete = Project::factory()->create();

    livewire(ListProjects::class)
        ->filterTable('readiness', 'needs_case_study')
        ->assertCanSeeTableRecords([$missingStory])
        ->assertCanNotSeeTableRecords([$complete]);
});
