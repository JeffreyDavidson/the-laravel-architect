<?php

use App\Enums\PublishStatus;
use App\Filament\Pages\MediaHealth;
use App\Models\Project;
use App\Models\User;
use App\Services\ImageUploadOptimizer;
use App\Services\ResponsiveImageVariants;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['is_admin' => true]));
});

it('shows source and responsive variant health for stored images', function () {
    $healthy = Project::withoutEvents(fn (): Project => Project::query()->create([
        'title' => 'Healthy project',
        'slug' => 'healthy-project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
    ]));
    $needsRepair = Project::withoutEvents(fn (): Project => Project::query()->create([
        'title' => 'Needs repair project',
        'slug' => 'needs-repair-project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
    ]));

    $healthyPath = app(ImageUploadOptimizer::class)->store(
        UploadedFile::fake()->image('healthy.jpg', 1600, 900),
        'projects',
        'public',
    );
    $needsRepairPath = app(ImageUploadOptimizer::class)->store(
        UploadedFile::fake()->image('needs-repair.jpg', 1600, 900),
        'projects',
        'public',
    );

    if (! is_string($healthyPath) || ! is_string($needsRepairPath)) {
        throw new RuntimeException('Expected optimized image paths.');
    }

    Project::withoutEvents(function () use ($healthy, $healthyPath, $needsRepair, $needsRepairPath): void {
        $healthy->update(['featured_image_path' => $healthyPath]);
        $needsRepair->update(['featured_image_path' => $needsRepairPath]);
    });

    app(ResponsiveImageVariants::class)->generate($healthyPath);

    livewire(MediaHealth::class)
        ->assertSee('Healthy project')
        ->assertSee('Needs repair project')
        ->assertSee('Healthy')
        ->assertSee('Needs repair')
        ->assertSee('Ready')
        ->assertSee('Missing');
});

it('repairs missing responsive variants for a stored image', function () {
    $project = Project::withoutEvents(fn (): Project => Project::query()->create([
        'title' => 'Repairable project',
        'slug' => 'repairable-project',
        'description' => 'Description',
        'status' => PublishStatus::Draft,
    ]));
    $path = app(ImageUploadOptimizer::class)->store(
        UploadedFile::fake()->image('repairable.jpg', 1600, 900),
        'projects',
        'public',
    );

    if (! is_string($path)) {
        throw new RuntimeException('Expected an optimized image path.');
    }

    Project::withoutEvents(fn (): bool => $project->update(['featured_image_path' => $path]));

    livewire(MediaHealth::class)
        ->callAction(TestAction::make('repair')->table("project:{$project->id}"))
        ->assertNotified();

    Storage::disk('public')->assertExists('projects/responsive/'.pathinfo($path, PATHINFO_FILENAME).'-640.webp');
    Storage::disk('public')->assertExists('projects/responsive/'.pathinfo($path, PATHINFO_FILENAME).'-1280.webp');
});
