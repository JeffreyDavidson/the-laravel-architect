<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

pest()->use(RefreshDatabase::class);

it('lists its technologies trimmed without blank or non-string entries', function (mixed $techStack, array $technologies) {
    $project = new Project(['tech_stack' => $techStack]);

    expect($project->technologies())->toBe($technologies);
})->with([
    'trimmed entries' => [['  Laravel ', "Filament\n"], ['Laravel', 'Filament']],
    'blank and non-string entries' => [['', " \t", 1, null, 'Pest'], ['Pest']],
    'no tech stack' => [null, []],
]);

it('publishes by status alone without a publish date', function () {
    // Arrange
    $project = PublishableFixtures::ready('project');

    // Act
    $project->publish();

    // Assert
    $project->refresh();

    expect($project->isPublished())
        ->toBeTrue()
        ->and($project->isScheduled())
        ->toBeFalse();
});
