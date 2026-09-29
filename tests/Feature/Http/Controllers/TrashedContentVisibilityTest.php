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
        $record instanceof Episode => route('podcast.episode', [$record->podcast, $record]),
        $record instanceof NewsletterIssue => route('newsletter.issue', $record),
        default => throw new InvalidArgumentException('Unsupported content type.'),
    };
}

it('removes trashed content from its page, the sitemap and search', function (string $type) {
    $record = PublishableFixtures::ready($type, ['published_at' => now()->subDay()]);
    $record->publish();
    $url = publicUrl($record);
    get($url)->assertOk();

    $record->delete();

    get($url)->assertNotFound();
    get(route('sitemap'))->assertDontSeeHtml($url);
    get(route('search', ['q' => 'Ready']))->assertDontSeeHtml($url);
})->with(['post', 'project', 'episode', 'newsletter issue']);

it('removes trashed posts and newsletter issues from their feeds', function (string $type, string $feed) {
    $record = PublishableFixtures::ready($type, ['published_at' => now()->subDay()]);
    $record->publish();
    $url = publicUrl($record);
    get(route($feed))->assertSeeHtml($url);

    $record->delete();

    get(route($feed))->assertDontSeeHtml($url);
})->with([
    'post' => ['post', 'rss'],
    'newsletter issue' => ['newsletter issue', 'newsletter.rss'],
]);

it('removes the episodes of a trashed podcast from the public site', function () {
    $episode = PublishableFixtures::ready('episode', ['published_at' => now()->subDay()]);
    $episode->publish();
    $url = publicUrl($episode);

    Podcast::query()
        ->sole()
        ->delete();

    get($url)->assertNotFound();
});
