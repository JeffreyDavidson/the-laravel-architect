<?php

use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

dataset('preview routes', [
    'post' => ['post', 'preview.post'],
    'project' => ['project', 'preview.project'],
    'episode' => ['episode', 'preview.episode'],
    'newsletter issue' => ['newsletter issue', 'preview.newsletterIssue'],
]);

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

    $response = get(route('preview.newsletterIssue', $issue));

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
