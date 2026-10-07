<?php

use App\Enums\ContentReadinessStatus;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('shows the readiness status and the missing details for a row', function () {
    $post = PublishableFixtures::readyPost();

    livewire(ListPosts::class)
        ->assertTableColumnStateSet('readiness', ContentReadinessStatus::NeedsAttention, $post)
        ->assertTableColumnHasDescription('readiness', 'Missing: Featured image, Tags', $post);
});

it('prefixes the missing details with the completed check count when showing progress', function () {
    $project = PublishableFixtures::ready('project', ['url' => 'https://example.com', 'tech_stack' => ['Laravel']]);

    livewire(ListProjects::class)
        ->assertTableColumnStateSet('readiness', ContentReadinessStatus::NeedsAttention, $project)
        ->assertTableColumnHasDescription('readiness', '4/6 complete · Missing: Featured image, Tags', $project);
});
