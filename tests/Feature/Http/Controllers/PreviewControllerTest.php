<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Project;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders signed previews for unpublished content', function () {
    $post = Post::factory()->create();
    $project = Project::factory()->create();
    $episode = Episode::factory()->create();
    $issue = NewsletterIssue::factory()->create();

    $previewUrlGenerator = app(PreviewUrlGenerator::class);

    get($previewUrlGenerator->for($post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Preview mode')
        ->assertSee('noindex, nofollow');
    get($previewUrlGenerator->for($project))
        ->assertOk()
        ->assertSee($project->title);
    get($previewUrlGenerator->for($episode))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Draft preview');
    get($previewUrlGenerator->for($issue))
        ->assertOk()
        ->assertSee($issue->title);
});

it('rejects unsigned preview URLs without revealing whether the draft exists', function (string $type, string $routeName) {
    $draft = PublishableFixtures::ready($type);

    $existingDraft = get(route($routeName, $draft->slug));
    $missingDraft = get(route($routeName, 'missing-draft'));

    $existingDraft->assertForbidden();
    $missingDraft->assertForbidden();
})->with('preview routes');

it('shows a rejected preview URL the branded forbidden page', function () {
    $issue = NewsletterIssue::factory()->create([
        'title' => 'Private Draft',
    ]);

    $response = get(route('preview.newsletter-issue', $issue));

    $response->assertForbidden()
        ->assertSeeText('The Laravel Architect')
        ->assertSeeText('This link can’t be opened.')
        ->assertSeeHtml('href="/"')
        ->assertSeeHtml('name="robots" content="noindex, nofollow"')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertDontSeeText('Try again')
        ->assertDontSeeText('Private Draft');
});

it('rejects expired preview URLs', function (string $type, string $routeName) {
    $draft = PublishableFixtures::ready($type);
    $expiredUrl = URL::temporarySignedRoute($routeName, now()->subMinute(), [$draft->slug]);

    $response = get($expiredUrl);

    $response->assertForbidden();
})->with('preview routes');

dataset('preview routes', [
    'post' => ['post', 'preview.post'],
    'project' => ['project', 'preview.project'],
    'episode' => ['episode', 'preview.episode'],
    'newsletter issue' => ['newsletter issue', 'preview.newsletter-issue'],
]);
