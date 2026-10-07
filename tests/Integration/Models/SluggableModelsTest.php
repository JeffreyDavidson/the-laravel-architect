<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('generates slugs from the configured source attributes', function () {
    $podcast = Podcast::factory()->create(['name' => 'The Architecture Podcast']);
    $category = Category::factory()->create(['name' => 'Laravel Architecture']);
    $episode = Episode::factory()
        ->for($podcast)
        ->create(['title' => 'Designing Clear Boundaries']);
    $post = Post::factory()->create(['title' => 'Structuring Laravel Applications']);
    $project = Project::factory()->create(['title' => 'A Laravel Project']);
    $video = Video::factory()->create(['title' => 'Laravel Video']);

    expect($category)
        ->slug->toBe('laravel-architecture')
        ->and($podcast->slug)
        ->toBe('the-architecture-podcast')
        ->and($episode->slug)
        ->toBe('designing-clear-boundaries')
        ->and($post->slug)
        ->toBe('structuring-laravel-applications')
        ->and($project->slug)
        ->toBe('a-laravel-project')
        ->and($video->slug)
        ->toBe('laravel-video');
});
