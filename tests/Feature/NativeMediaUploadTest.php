<?php

use App\Enums\ProjectStatus;
use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->seed(ShieldSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

it('stores a validated image through the Filament project form', function () {
    Livewire::test(CreateProject::class)
        ->fillForm([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => ProjectStatus::Draft,
            'featured_image_path' => UploadedFile::fake()->image('project.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $path = Project::query()->sole()->featured_image_path;

    expect($path)->toStartWith('projects/');
    Storage::disk('public')->assertExists($path);
});

it('rejects an oversized image through the Filament project form', function () {
    Livewire::test(CreateProject::class)
        ->fillForm([
            'title' => 'Project',
            'slug' => 'project',
            'description' => 'Description',
            'status' => ProjectStatus::Draft,
            'featured_image_path' => UploadedFile::fake()->image('project.jpg')->size(10241),
        ])
        ->call('create')
        ->assertHasFormErrors(['featured_image_path']);

    expect(Project::query()->exists())->toBeFalse();
});

it('stores validated audio through the Filament episode form', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
    ]);

    Livewire::test(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode',
            'slug' => 'episode',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'audio_path' => UploadedFile::fake()->create('episode.mp3', 1000, 'audio/mpeg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $path = Episode::query()->sole()->audio_path;

    expect($path)->toStartWith('episodes/audio/');
    Storage::disk('public')->assertExists($path);
});

it('rejects non-audio files through the Filament episode form', function () {
    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
    ]);

    Livewire::test(CreateEpisode::class)
        ->fillForm([
            'podcast_id' => $podcast->id,
            'title' => 'Episode',
            'slug' => 'episode',
            'description' => 'Description',
            'status' => PublishStatus::Draft,
            'audio_path' => UploadedFile::fake()->create('payload.php', 1, 'application/x-php'),
        ])
        ->call('create')
        ->assertHasFormErrors(['audio_path']);

    expect(Episode::query()->exists())->toBeFalse();
});
