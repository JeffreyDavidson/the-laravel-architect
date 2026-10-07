<?php

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['is_admin' => true]);
    actingAs($user);
});

it('renders the project create page for an authorized user', function () {
    get(ProjectResource::getUrl('create'))
        ->assertOk();
});

it('renders the project edit page for an authorized user', function () {
    $project = Project::factory()->create();

    get(ProjectResource::getUrl('edit', ['record' => $project]))
        ->assertOk();
});
