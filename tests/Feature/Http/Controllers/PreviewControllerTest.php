<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

pest()->use(RefreshDatabase::class);

it('renders signed previews for unpublished content', function () {
    $author = User::query()->create([
        'name' => 'Jeffrey Davidson',
        'email' => 'preview@example.com',
        'password' => 'password',
    ]);
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
    $post = Post::query()->create([
        'title' => 'Draft Post Preview',
        'slug' => 'draft-post-preview',
        'excerpt' => 'A draft post.',
        'content' => 'Draft post content.',
        'category_id' => $category->id,
        'user_id' => $author->id,
        'status' => PublishStatus::Draft,
    ]);
    $project = Project::query()->create([
        'title' => 'Draft Project Preview',
        'slug' => 'draft-project-preview',
        'description' => 'A draft project.',
        'content' => 'Draft project content.',
        'status' => PublishStatus::Draft,
    ]);
    $podcast = Podcast::query()->create([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'description' => 'Conversations about Laravel architecture.',
        'is_active' => true,
    ]);
    $episode = Episode::query()->create([
        'podcast_id' => $podcast->id,
        'title' => 'Draft Episode Preview',
        'slug' => 'draft-episode-preview',
        'description' => 'A draft episode.',
        'status' => PublishStatus::Draft,
    ]);
    $issue = NewsletterIssue::query()->create([
        'title' => 'Draft Newsletter Preview',
        'slug' => 'draft-newsletter-preview',
        'excerpt' => 'A draft newsletter.',
        'content' => 'Draft newsletter content.',
        'status' => PublishStatus::Draft,
    ]);

    $previewUrlGenerator = app(PreviewUrlGenerator::class);

    $this->get($previewUrlGenerator->for($post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Preview mode')
        ->assertSee('noindex, nofollow');
    $this->get($previewUrlGenerator->for($project))
        ->assertOk()
        ->assertSee($project->title);
    $this->get($previewUrlGenerator->for($episode))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Draft preview');
    $this->get($previewUrlGenerator->for($issue))
        ->assertOk()
        ->assertSee($issue->title);
});

it('rejects unsigned preview URLs', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Private Draft',
        'slug' => 'private-draft',
        'content' => 'Not public.',
        'status' => PublishStatus::Draft,
    ]);

    $this->get(route('preview.newsletter-issue', $issue))
        ->assertForbidden();
});

it('rejects expired preview URLs', function () {
    $issue = NewsletterIssue::query()->create([
        'title' => 'Expired Draft',
        'slug' => 'expired-draft',
        'content' => 'Not public.',
        'status' => PublishStatus::Draft,
    ]);

    $expiredUrl = URL::temporarySignedRoute(
        'preview.newsletter-issue',
        now()->subMinute(),
        ['newsletterIssue' => $issue],
    );

    $this->get($expiredUrl)
        ->assertForbidden();
});
