<?php

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('filters projects that are ready to publish', function () {
    $ready = Project::query()->create([
        'title' => 'Ready project',
        'slug' => 'ready-project',
        'description' => 'A project description.',
        'content' => 'A complete case study.',
        'featured_image_path' => 'projects/ready.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel'],
    ]);
    $ready->attachTag(Tag::query()->create(['name' => 'Laravel', 'slug' => 'laravel']));
    $incomplete = Project::query()->create([
        'title' => 'Incomplete project',
        'slug' => 'incomplete-project',
        'description' => 'A project description.',
    ]);

    livewire(ListProjects::class)
        ->filterTable('readiness', 'ready')
        ->assertCanSeeTableRecords([$ready])
        ->assertCanNotSeeTableRecords([$incomplete]);
});

it('filters projects missing a case study', function () {
    $missingStory = Project::query()->create([
        'title' => 'Missing story project',
        'slug' => 'missing-story-project',
        'description' => 'A project description.',
    ]);
    $complete = Project::query()->create([
        'title' => 'Complete story project',
        'slug' => 'complete-story-project',
        'description' => 'A project description.',
        'content' => 'A complete case study.',
    ]);

    livewire(ListProjects::class)
        ->filterTable('readiness', 'needs_case_study')
        ->assertCanSeeTableRecords([$missingStory])
        ->assertCanNotSeeTableRecords([$complete]);
});
