<?php

use App\Enums\ContentReadinessStatus;
use App\Filament\Resources\Podcasts\Pages\ListPodcasts;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\Posts\Pages\ListPosts;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()->create(['is_admin' => true])));

it('shows the readiness status and the missing details for a row', function () {
    $podcast = Podcast::factory()->create([
        'long_description' => 'About the show.',
        'apple_url' => 'https://podcasts.example.test/show',
    ]);

    livewire(ListPodcasts::class)
        ->assertTableColumnStateSet('readiness', ContentReadinessStatus::NeedsAttention, $podcast)
        ->assertTableColumnHasDescription('readiness', 'Missing: Cover image', $podcast);
});

it('shows only what blocks publishing on the posts and episodes lists', function () {
    $ready = PublishableFixtures::readyPost();
    $blocked = Post::factory()->create(['excerpt' => null, 'category_id' => null]);

    livewire(ListPosts::class)
        ->assertTableColumnStateSet('readiness', 'Ready', $ready)
        ->assertTableColumnHasDescription('readiness', 'All required details are complete.', $ready);
    livewire(ListPosts::class)
        ->assertTableColumnStateSet('readiness', '2 to fix', $blocked)
        ->assertTableColumnHasDescription('readiness', 'Missing: Excerpt, Category', $blocked);
});

it('prefixes the missing details with the completed check count when showing progress', function () {
    $project = PublishableFixtures::ready('project', ['url' => 'https://example.com', 'tech_stack' => ['Laravel']]);

    livewire(ListProjects::class)
        ->assertTableColumnStateSet('readiness', ContentReadinessStatus::NeedsAttention, $project)
        ->assertTableColumnHasDescription('readiness', '4/6 complete · Missing: Featured image, Tags', $project);
});

it('says every public detail is complete for a ready row', function () {
    $project = Project::factory()->create([
        'featured_image_path' => 'projects/complete.webp',
        'url' => 'https://example.com',
        'tech_stack' => ['Laravel'],
    ]);
    $project->attachTag(Tag::factory()->create());

    livewire(ListProjects::class)
        ->assertTableColumnStateSet('readiness', ContentReadinessStatus::Ready, $project)
        ->assertTableColumnHasDescription('readiness', '6/6 complete · All public details are complete.', $project);
});
