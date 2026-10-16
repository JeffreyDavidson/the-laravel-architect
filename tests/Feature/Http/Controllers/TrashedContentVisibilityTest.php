<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

function publicUrl(Model $record): string
{
    return match (true) {
        $record instanceof Post => route('blog.show', $record),
        $record instanceof Project => route('projects.show', $record),
        $record instanceof Episode => route('podcasts.episode', [$record->podcast, $record]),
        $record instanceof NewsletterIssue => route('newsletter.issue', $record),
        default => throw new InvalidArgumentException('Unsupported content type.'),
    };
}

it('removes trashed content from its page, the sitemap and search', function (string $type) {
    // Arrange: the published record is reachable on every surface before it is trashed
    $record = PublishableFixtures::ready($type, ['published_at' => now()->subDay()]);
    $record->publish();
    $url = publicUrl($record);
    get($url)->assertOk();
    get(route('sitemap'))->assertSeeHtml($url);
    get(route('search', ['q' => 'Ready']))->assertSeeHtml($url);

    // Act
    $record->delete();

    // Assert
    $response = get($url);
    $response->assertNotFound();
    $response = get(route('sitemap'));
    $response->assertDontSeeHtml($url);
    $response = get(route('search', ['q' => 'Ready']));
    $response->assertDontSeeHtml($url);
})->with(['post', 'project', 'episode', 'newsletter issue']);

it('removes trashed posts and newsletter issues from their feeds', function (string $type, string $feed) {
    // Arrange: the published record is in its feed before it is trashed
    $record = PublishableFixtures::ready($type, ['published_at' => now()->subDay()]);
    $record->publish();
    $url = publicUrl($record);
    get(route($feed))->assertSeeHtml($url);

    // Act
    $record->delete();

    // Assert
    $response = get(route($feed));
    $response->assertDontSeeHtml($url);
})->with([
    'post' => ['post', 'rss'],
    'newsletter issue' => ['newsletter issue', 'newsletter.rss'],
]);

it('removes the episodes of a trashed podcast from the public site', function () {
    // Arrange: the episode page is live before its podcast is trashed
    $episode = PublishableFixtures::ready('episode', ['published_at' => now()->subDay()]);
    $episode->publish();
    $url = publicUrl($episode);
    get($url)->assertOk();

    // Act
    Podcast::query()
        ->sole()
        ->delete();

    // Assert
    $response = get($url);
    $response->assertNotFound();
});
