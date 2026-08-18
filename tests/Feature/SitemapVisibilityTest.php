<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('only includes public content in the sitemap', function () {
    $user = User::query()->create([
        'name' => 'Jeffrey Davidson',
        'email' => 'jeffrey@example.test',
        'password' => bcrypt('password'),
    ]);

    $publishedPost = Post::query()->create([
        'title' => 'Published Sitemap Post',
        'slug' => 'published-sitemap-post',
        'excerpt' => 'Visible post',
        'content' => 'Visible post content',
        'user_id' => $user->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $scheduledPost = Post::query()->create([
        'title' => 'Scheduled Sitemap Post',
        'slug' => 'scheduled-sitemap-post',
        'content' => 'Scheduled post content',
        'user_id' => $user->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $publishedProject = Project::query()->create([
        'title' => 'Published Sitemap Project',
        'slug' => 'published-sitemap-project',
        'description' => 'Visible project',
        'status' => 'published',
    ]);

    $draftProject = Project::query()->create([
        'title' => 'Draft Sitemap Project',
        'slug' => 'draft-sitemap-project',
        'description' => 'Hidden project',
        'status' => 'draft',
    ]);

    $podcast = Podcast::query()->create([
        'name' => 'Coffee With The Laravel Architect',
        'slug' => 'coffee-with-the-laravel-architect',
        'description' => 'Laravel conversations',
        'is_active' => true,
    ]);

    $publishedEpisode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Published Sitemap Episode',
        'slug' => 'published-sitemap-episode',
        'description' => 'Visible episode',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $draftEpisode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Draft Sitemap Episode',
        'slug' => 'draft-sitemap-episode',
        'description' => 'Hidden episode',
        'status' => PublishStatus::Draft,
    ]);

    $draftOnlyCategory = Category::query()->create(['name' => 'Private', 'slug' => 'private']);
    $scheduledPost->update(['category_id' => $draftOnlyCategory->id]);

    DB::table('posts')->where('id', $publishedPost->id)->update(['updated_at' => null]);
    DB::table('projects')->where('id', $publishedProject->id)->update(['updated_at' => null]);
    DB::table('episodes')->where('id', $publishedEpisode->id)->update(['updated_at' => null]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('blog.show', $publishedPost), false)
        ->assertDontSee(route('blog.show', $scheduledPost), false)
        ->assertSee(route('projects.show', $publishedProject), false)
        ->assertDontSee(route('projects.show', $draftProject), false)
        ->assertDontSee(route('blog.category', $draftOnlyCategory), false)
        ->assertSee(route('podcast.episode', [$podcast, $publishedEpisode]), false)
        ->assertDontSee(route('podcast.episode', [$podcast, $draftEpisode]), false)
        ->assertDontSee('<lastmod>', false);
});
