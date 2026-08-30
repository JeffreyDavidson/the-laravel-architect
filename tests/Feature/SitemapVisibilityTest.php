<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
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

    $publishedTag = Tag::query()->create(['name' => 'Public tag', 'slug' => 'public-tag']);
    $scheduledOnlyTag = Tag::query()->create(['name' => 'Scheduled tag', 'slug' => 'scheduled-tag']);
    $publishedPost->attachTag($publishedTag);
    $scheduledPost->attachTag($scheduledOnlyTag);

    DB::table('posts')->where('id', $publishedPost->id)->update(['updated_at' => null]);
    DB::table('projects')->where('id', $publishedProject->id)->update(['updated_at' => null]);
    DB::table('podcasts')->where('id', $podcast->id)->update(['updated_at' => null]);
    DB::table('episodes')->where('id', $publishedEpisode->id)->update(['updated_at' => null]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('blog.show', $publishedPost), false)
        ->assertDontSee(route('blog.show', $scheduledPost), false)
        ->assertSee(route('projects.show', $publishedProject), false)
        ->assertDontSee(route('projects.show', $draftProject), false)
        ->assertDontSee(route('blog.category', $draftOnlyCategory), false)
        ->assertSee(route('blog.tag', $publishedTag), false)
        ->assertDontSee(route('blog.tag', $scheduledOnlyTag), false)
        ->assertSee(route('podcast.episode', [$podcast, $publishedEpisode]), false)
        ->assertDontSee(route('podcast.episode', [$podcast, $draftEpisode]), false)
        ->assertDontSee('<lastmod>', false);
});

it('reports the latest published content change for sitemap archives', function () {
    $user = User::query()->create([
        'name' => 'Jeffrey Davidson',
        'email' => 'jeffrey@example.test',
        'password' => bcrypt('password'),
    ]);
    $category = Category::query()->create([
        'name' => 'Architecture',
        'slug' => 'architecture',
    ]);
    $tag = Tag::query()->create([
        'name' => 'Boundaries',
        'slug' => 'boundaries',
    ]);
    $post = Post::query()->create([
        'title' => 'Published Architecture Post',
        'slug' => 'published-architecture-post',
        'content' => 'Visible post content',
        'category_id' => $category->id,
        'user_id' => $user->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    $post->attachTag($tag);

    $project = Project::query()->create([
        'title' => 'Published Architecture Project',
        'slug' => 'published-architecture-project',
        'description' => 'Visible project',
        'status' => 'published',
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Laravel conversations',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Published Architecture Episode',
        'slug' => 'published-architecture-episode',
        'description' => 'Visible episode',
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $postUpdatedAt = now()->subDays(4)->startOfSecond();
    $projectUpdatedAt = now()->subDays(3)->startOfSecond();
    $podcastUpdatedAt = now()->subDays(2)->startOfSecond();
    $episodeUpdatedAt = now()->subDay()->startOfSecond();

    DB::table('posts')->where('id', $post->id)->update(['updated_at' => $postUpdatedAt]);
    DB::table('projects')->where('id', $project->id)->update(['updated_at' => $projectUpdatedAt]);
    DB::table('podcasts')->where('id', $podcast->id)->update(['updated_at' => $podcastUpdatedAt]);
    DB::table('episodes')->where('id', $episode->id)->update(['updated_at' => $episodeUpdatedAt]);

    $response = $this->get(route('sitemap'))
        ->assertOk();

    foreach ([
        [route('blog.index'), $postUpdatedAt],
        [route('blog.category', $category), $postUpdatedAt],
        [route('blog.tag', $tag), $postUpdatedAt],
        [route('projects.index'), $projectUpdatedAt],
        [route('podcast.index'), $episodeUpdatedAt],
        [route('podcast.show', $podcast), $episodeUpdatedAt],
    ] as [$url, $updatedAt]) {
        $response->assertSee(
            '<loc>'.$url.'</loc><lastmod>'.$updatedAt->toW3cString().'</lastmod>',
            false,
        );
    }
});
