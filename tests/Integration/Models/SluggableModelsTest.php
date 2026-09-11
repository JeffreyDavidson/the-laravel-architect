<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates slugs from the configured source attributes', function () {
    $podcast = Podcast::query()->create([
        'name' => 'The Architecture Podcast',
        'description' => 'Podcast description',
    ]);
    $user = User::factory()->create();

    expect(Category::query()->create(['name' => 'Laravel Architecture']))
        ->slug->toBe('laravel-architecture')
        ->and($podcast->slug)->toBe('the-architecture-podcast')
        ->and(Episode::query()->create([
            'podcast_id' => $podcast->id,
            'title' => 'Designing Clear Boundaries',
            'description' => 'Episode description',
            'status' => PublishStatus::Draft,
        ])->slug)->toBe('designing-clear-boundaries')
        ->and(Post::query()->create([
            'title' => 'Structuring Laravel Applications',
            'content' => 'Post content',
            'user_id' => $user->id,
            'status' => PublishStatus::Draft,
        ])->slug)->toBe('structuring-laravel-applications')
        ->and(Project::query()->create([
            'title' => 'A Laravel Project',
            'description' => 'Project description',
            'content' => 'Project content',
            'status' => PublishStatus::Draft,
        ])->slug)->toBe('a-laravel-project')
        ->and(Video::query()->create([
            'youtube_id' => 'video-id',
            'title' => 'Laravel Video',
        ])->slug)->toBe('laravel-video');
});
