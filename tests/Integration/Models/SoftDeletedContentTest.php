<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RalphJSmit\Laravel\SEO\Models\SEO;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\travel;
use function Pest\Laravel\travelBack;
use function Pest\Laravel\travelTo;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('public'));

/**
 * Create a record of the given type that owns a stored image, an SEO row and, where the
 * type supports tags, a tag link.
 */
function contentWithOwnedData(string $type): Post|Project|Episode|NewsletterIssue|Podcast
{
    if ($type === 'podcast') {
        Storage::disk('public')->put('podcasts/cover.png', 'image');

        return Podcast::factory()->create(['cover_image_path' => 'podcasts/cover.png']);
    }

    $imagePath = str_replace(' ', '-', $type).'/image.png';
    Storage::disk('public')->put($imagePath, 'image');
    $attributes = $type === 'newsletter issue' ? [] : ['featured_image_path' => $imagePath];
    $record = PublishableFixtures::ready($type, $attributes);

    if (method_exists($record, 'attachTag')) {
        $record->attachTag('Laravel');
    }

    return $record;
}

function ownedImagePath(Model $record): ?string
{
    $path = $record->getAttribute($record instanceof Podcast ? 'cover_image_path' : 'featured_image_path');

    return is_string($path) ? $path : null;
}

function tagLinkCount(Model $record): int
{
    return DB::table('taggables')
        ->where('taggable_type', $record->getMorphClass())
        ->where('taggable_id', $record->getKey())
        ->count();
}

function seoRowCount(Model $record): int
{
    return SEO::query()
        ->where('model_type', $record->getMorphClass())
        ->where('model_id', $record->getKey())
        ->count();
}

dataset('soft deleting content', ['post', 'project', 'episode', 'newsletter issue', 'podcast']);

it('moves deleted content to the trash while keeping everything it owns', function (string $type) {
    $record = contentWithOwnedData($type);
    $tags = tagLinkCount($record);

    $record->delete();

    expect($record->trashed())
        ->toBeTrue()
        ->and($record::query()->find($record->getKey()))
        ->toBeNull()
        ->and($record::withTrashed()->find($record->getKey()))
        ->not->toBeNull()
        ->and(seoRowCount($record))
        ->toBe(1)
        ->and(tagLinkCount($record))
        ->toBe($tags);

    if (ownedImagePath($record) !== null) {
        Storage::disk('public')->assertExists(ownedImagePath($record));
    }
})->with('soft deleting content');

it('restores trashed content with everything it owns', function (string $type) {
    $record = contentWithOwnedData($type);
    $tags = tagLinkCount($record);
    $record->delete();

    $record->restore();

    expect($record::query()->find($record->getKey()))
        ->not->toBeNull()
        ->and(seoRowCount($record))
        ->toBe(1)
        ->and(tagLinkCount($record))
        ->toBe($tags);
})->with('soft deleting content');

it('removes owned files, the SEO row and tag links only when force deleted', function (string $type) {
    $record = contentWithOwnedData($type);
    $record->delete();

    $record->forceDelete();

    expect($record::withTrashed()->find($record->getKey()))
        ->toBeNull()
        ->and(seoRowCount($record))
        ->toBe(0)
        ->and(tagLinkCount($record))
        ->toBe(0);

    if (ownedImagePath($record) !== null) {
        Storage::disk('public')->assertMissing(ownedImagePath($record));
    }
})->with('soft deleting content');

it('never reuses the slug of trashed content', function () {
    $trashed = PublishableFixtures::ready('post');
    $trashed->delete();

    $post = Post::factory()->create(['title' => 'Ready post']);

    expect($post->slug)
        ->not->toBe($trashed->getAttribute('slug'));
});

it('trashes a podcast together with its episodes', function () {
    $episode = PublishableFixtures::ready('episode');
    $podcast = $episode->getAttribute('podcast');

    if (! $podcast instanceof Podcast) {
        throw new UnexpectedValueException('The episode fixture has no podcast.');
    }

    $podcast->delete();

    expect(Episode::query()->find($episode->getKey()))
        ->toBeNull()
        ->and(Episode::onlyTrashed()
            ->whereKey($episode->getKey())
            ->exists())
        ->toBeTrue();
});

it('restores only the episodes that were trashed with their podcast', function () {
    $episode = PublishableFixtures::ready('episode');
    $podcast = $episode->getAttribute('podcast');

    if (! $podcast instanceof Podcast) {
        throw new UnexpectedValueException('The episode fixture has no podcast.');
    }

    $trashedEarlier = Episode::factory()
        ->for($podcast)
        ->create();
    travel(-1)->days();
    $trashedEarlier->delete();
    travelBack();
    $podcast->delete();

    $podcast->restore();

    expect(Episode::query()->find($episode->getKey()))
        ->not->toBeNull()
        ->and(Episode::query()->find($trashedEarlier->getKey()))
        ->toBeNull();
});

it('restores every episode trashed with its podcast when the delete spans several seconds', function () {
    travelTo('2026-10-01 12:00:00');
    $podcast = Podcast::factory()->create();
    $trashedEarlier = Episode::factory()
        ->for($podcast)
        ->create();
    $trashedEarlier->delete();
    travel(1)->seconds();
    $episodeIds = [];

    foreach (range(1, 3) as $number) {
        $episodeIds[] = Episode::factory()
            ->for($podcast)
            ->create()
            ->id;
    }

    Episode::deleted(function (): void {
        travel(1)->seconds();
    });
    $podcast->delete();

    $podcast->restore();

    expect(Episode::query()
        ->whereKey($episodeIds)
        ->count())
        ->toBe(3)
        ->and(Episode::onlyTrashed()
            ->whereKey($trashedEarlier->id)
            ->exists())
        ->toBeTrue();
});

it('permanently deletes every episode when a podcast is force deleted', function () {
    $episode = PublishableFixtures::ready('episode');
    $podcast = $episode->getAttribute('podcast');

    if (! $podcast instanceof Podcast) {
        throw new UnexpectedValueException('The episode fixture has no podcast.');
    }

    $podcast->delete();
    $podcast->forceDelete();

    expect(Episode::withTrashed()->find($episode->getKey()))
        ->toBeNull()
        ->and(seoRowCount($episode))
        ->toBe(0);
});
