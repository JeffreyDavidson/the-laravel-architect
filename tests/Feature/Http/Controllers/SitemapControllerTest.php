<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('includes the newsletter archive and only public newsletter issues', function () {
    $published = NewsletterIssue::factory()->published()
        ->create();
    $draft = NewsletterIssue::factory()->create(['slug' => 'excluded-draft-issue']);

    get(route('sitemap'))
        ->assertSeeHtml(route('newsletter.index'))
        ->assertSeeHtml(route('newsletter.issue', $published))
        ->assertDontSeeHtml(route('newsletter.issue', $draft));
});

it('only includes public content in the sitemap', function () {
    $publishedPost = Post::factory()->published()
        ->create();

    $draftOnlyCategory = Category::factory()->create(['name' => 'Excluded draft-only category']);
    $scheduledPost = Post::factory()->for($draftOnlyCategory)
        ->published()
        ->create([
            'slug' => 'excluded-scheduled-post',
            'published_at' => now()->addDay(),
        ]);

    $publishedProject = Project::factory()->published()
        ->create();

    $draftProject = Project::factory()->create(['slug' => 'excluded-draft-project']);

    $podcast = Podcast::factory()->create();

    $publishedEpisode = Episode::factory()->for($podcast)
        ->published()
        ->create();

    $draftEpisode = Episode::factory()->for($podcast)
        ->create(['slug' => 'excluded-draft-episode']);

    $publishedTag = Tag::factory()->create();
    $scheduledOnlyTag = Tag::factory()->create(['name' => 'Excluded scheduled-only tag']);
    $publishedPost->attachTag($publishedTag);
    $scheduledPost->attachTag($scheduledOnlyTag);

    DB::table('posts')->where('id', $publishedPost->id)
        ->update(['updated_at' => null]);
    DB::table('projects')->where('id', $publishedProject->id)
        ->update(['updated_at' => null]);
    DB::table('podcasts')->where('id', $podcast->id)
        ->update(['updated_at' => null]);
    DB::table('episodes')->where('id', $publishedEpisode->id)
        ->update(['updated_at' => null]);

    get('/sitemap.xml')
        ->assertOk()
        ->assertSeeHtml(route('blog.show', $publishedPost))
        ->assertDontSeeHtml(route('blog.show', $scheduledPost))
        ->assertSeeHtml(route('projects.show', $publishedProject))
        ->assertDontSeeHtml(route('projects.show', $draftProject))
        ->assertDontSeeHtml(route('blog.category', $draftOnlyCategory))
        ->assertSeeHtml(route('blog.tag', $publishedTag))
        ->assertDontSeeHtml(route('blog.tag', $scheduledOnlyTag))
        ->assertSeeHtml(route('podcasts.episode', [$podcast, $publishedEpisode]))
        ->assertDontSeeHtml(route('podcasts.episode', [$podcast, $draftEpisode]))
        ->assertDontSeeHtml('<lastmod>');
});

it('reports the latest published content change for sitemap archives', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = Post::factory()->for($category)
        ->published()
        ->create();
    $post->attachTag($tag);

    $project = Project::factory()->published()
        ->create();
    $podcast = Podcast::factory()->create();
    $episode = Episode::factory()->for($podcast)
        ->published()
        ->create();

    $postUpdatedAt = now()->subDays(4)
        ->startOfSecond();
    $projectUpdatedAt = now()->subDays(3)
        ->startOfSecond();
    $podcastUpdatedAt = now()->subDays(2)
        ->startOfSecond();
    $episodeUpdatedAt = now()->subDay()
        ->startOfSecond();

    DB::table('posts')->where('id', $post->id)
        ->update(['updated_at' => $postUpdatedAt]);
    DB::table('projects')->where('id', $project->id)
        ->update(['updated_at' => $projectUpdatedAt]);
    DB::table('podcasts')->where('id', $podcast->id)
        ->update(['updated_at' => $podcastUpdatedAt]);
    DB::table('episodes')->where('id', $episode->id)
        ->update(['updated_at' => $episodeUpdatedAt]);

    $response = get(route('sitemap'))
        ->assertOk();

    foreach ([
        [route('blog.index'), $postUpdatedAt],
        [route('blog.category', $category), $postUpdatedAt],
        [route('blog.tag', $tag), $postUpdatedAt],
        [route('projects.index'), $projectUpdatedAt],
        [route('podcasts.index'), $episodeUpdatedAt],
        [route('podcasts.show', $podcast), $episodeUpdatedAt],
        [route('home'), $episodeUpdatedAt],
        [route('archive.index'), $episodeUpdatedAt],
    ] as [$url, $updatedAt]) {
        $response->assertSeeHtml('<loc>'.$url.'</loc><lastmod>'.$updatedAt->toW3cString().'</lastmod>');
    }
});

it('serves the sitemap to crawlers without starting a session or setting cookies', function () {
    config()->set('session.driver', 'database');

    $response = get(route('sitemap'));

    $response
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');
    expect($response->headers->getCookies())
        ->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});
