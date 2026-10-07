<?php

use App\Models\Project;

it('lists its technologies trimmed without blank or non-string entries', function (mixed $techStack, array $technologies) {
    $project = new Project(['tech_stack' => $techStack]);

    expect($project->technologies())->toBe($technologies);
})->with([
    'trimmed entries' => [['  Laravel ', "Filament\n"], ['Laravel', 'Filament']],
    'blank and non-string entries' => [['', " \t", 1, null, 'Pest'], ['Pest']],
    'no tech stack' => [null, []],
]);
