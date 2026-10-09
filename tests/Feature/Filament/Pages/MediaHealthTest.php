<?php

use App\Enums\MediaHealthStatus;
use App\Filament\Pages\MediaHealth;
use App\Models\Project;
use App\Models\User;
use App\Services\ImageUploadOptimizer;
use Filament\Actions\Testing\TestAction;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Services\Media\ResponsiveImageVariants;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('shows source and responsive variant health for stored images', function () {
    $healthy = Project::withoutEvents(fn (): Project => Project::factory()->create([
        'title' => 'Healthy project',
        'slug' => 'healthy-project',
    ]));
    $needsRepair = Project::withoutEvents(fn (): Project => Project::factory()->create([
        'title' => 'Needs repair project',
        'slug' => 'needs-repair-project',
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

it('filters stored images by health status and labels each status', function () {
    $missingImage = Project::withoutEvents(fn (): Project => Project::factory()->create(['slug' => 'missing-image-project']));
    $needsRepair = Project::withoutEvents(fn (): Project => Project::factory()->create(['slug' => 'needs-repair-project']));
    $path = app(ImageUploadOptimizer::class)->store(
        UploadedFile::fake()->image('needs-repair.jpg', 1600, 900),
        'projects',
        'public',
    );

    if (! is_string($path)) {
        throw new RuntimeException('Expected an optimized image path.');
    }

    Project::withoutEvents(fn (): bool => $needsRepair->update(['featured_image_path' => $path]));
    $needsRepairKey = "project:{$needsRepair->id}";

    livewire(MediaHealth::class)
        ->filterTable('status', MediaHealthStatus::NeedsRepair->value)
        ->assertCanSeeTableRecords([$needsRepairKey])
        ->assertCanNotSeeTableRecords(["project:{$missingImage->id}"])
        ->assertTableColumnFormattedStateSet('source_status', 'Optimized', $needsRepairKey)
        ->assertTableColumnFormattedStateSet('variants', 'Missing', $needsRepairKey)
        ->assertTableColumnFormattedStateSet('status', 'Needs repair', $needsRepairKey);
});

it('repairs missing responsive variants for a stored image', function () {
    $project = Project::withoutEvents(fn (): Project => Project::factory()->create(['slug' => 'repairable-project']));
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

it('withholds variant repair from a user who is not an administrator', function () {
    $project = Project::withoutEvents(fn (): Project => Project::factory()->create(['slug' => 'repairable-project']));
    $path = app(ImageUploadOptimizer::class)->store(
        UploadedFile::fake()->image('repairable.jpg', 1600, 900),
        'projects',
        'public',
    );

    if (! is_string($path)) {
        throw new RuntimeException('Expected an optimized image path.');
    }

    Project::withoutEvents(fn (): bool => $project->update(['featured_image_path' => $path]));
    $repair = TestAction::make('repair')->table("project:{$project->id}");
    $page = livewire(MediaHealth::class)
        ->assertActionVisible($repair);

    // The panel middleware already turns non-administrators away; skip it so the action's own authorization is what refuses the repair.
    withoutMiddleware(Authenticate::class)
        ->actingAs(User::factory()->create(['is_admin' => false]));

    $page->assertActionHidden($repair)
        ->mountAction($repair)
        ->call('callMountedAction');

    Storage::disk('public')->assertMissing('projects/responsive/'.pathinfo($path, PATHINFO_FILENAME).'-640.webp');
});
