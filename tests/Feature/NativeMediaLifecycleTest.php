<?php

use App\Enums\ProjectStatus;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('deletes the previous file when native media is replaced', function () {
    Storage::disk('public')->put('projects/old.png', 'old');
    Storage::disk('public')->put('projects/new.png', 'new');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/old.png',
    ]);

    $project->update(['featured_image_path' => 'projects/new.png']);

    Storage::disk('public')->assertMissing('projects/old.png');
    Storage::disk('public')->assertExists('projects/new.png');
});

it('keeps native media when unrelated attributes change', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/image.png',
    ]);

    $project->update(['title' => 'Renamed Project']);

    Storage::disk('public')->assertExists('projects/image.png');
});

it('deletes native media with its record', function () {
    Storage::disk('public')->put('projects/image.png', 'image');

    $project = Project::query()->create([
        'title' => 'Project',
        'slug' => 'project',
        'description' => 'Description',
        'status' => ProjectStatus::Draft,
        'featured_image_path' => 'projects/image.png',
    ]);

    $project->delete();

    Storage::disk('public')->assertMissing('projects/image.png');
});

it('deletes episode media when its podcast is deleted', function () {
    Storage::disk('public')->put('podcasts/cover.png', 'cover');
    Storage::disk('public')->put('episodes/images/episode.png', 'image');
    Storage::disk('public')->put('episodes/audio/episode.mp3', 'audio');

    $podcast = Podcast::query()->create([
        'name' => 'Podcast',
        'slug' => 'podcast',
        'description' => 'Description',
        'cover_image_path' => 'podcasts/cover.png',
    ]);
    Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Episode',
        'slug' => 'episode',
        'description' => 'Description',
        'featured_image_path' => 'episodes/images/episode.png',
        'audio_path' => 'episodes/audio/episode.mp3',
    ]);

    $podcast->delete();

    Storage::disk('public')->assertMissing([
        'podcasts/cover.png',
        'episodes/images/episode.png',
        'episodes/audio/episode.mp3',
    ]);
});
