<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Services\OgImageCache;
use App\Services\OgImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

it('generates an OG image once and serves later requests from private storage', function () {
    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::type(Post::class))
        ->returns('generated-png');
    app()->instance(OgImageGenerator::class, $generator);

    get(route('ogImage', $post))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Cache-Control', 'max-age=86400, public')
        ->assertContent('generated-png');
    get(route('ogImage', $post))
        ->assertOk()
        ->assertContent('generated-png');

    Storage::disk('local')->assertExists([
        "og-images/{$post->id}/image.png",
        "og-images/{$post->id}/signature",
    ]);
});

it('regenerates an OG image when rendered post data changes', function () {
    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    app()->instance(OgImageGenerator::class, $generator);

    get(route('ogImage', $post))
        ->assertContent('first-png');

    $post->update(['title' => 'Updated title']);

    get(route('ogImage', $post))
        ->assertContent('updated-png');
});

it('regenerates an OG image when its category name changes', function () {
    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    app()->instance(OgImageGenerator::class, $generator);

    get(route('ogImage', $post))
        ->assertContent('first-png');

    $post->category()
        ->firstOrFail()
        ->update(['name' => 'Updated category']);

    get(route('ogImage', $post))
        ->assertContent('updated-png');
});

it('serves OG images to crawlers without starting a session or setting cookies', function () {
    config()->set('session.driver', 'database');
    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->returns('generated-png');
    app()->instance(OgImageGenerator::class, $generator);

    $response = get(route('ogImage', $post));

    $response
        ->assertOk()
        ->assertContent('generated-png')
        ->assertHeaderMissing('Set-Cookie');
    expect($response->headers->getCookies())
        ->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});

it('returns not found without a session for a post that is not published', function () {
    config()->set('session.driver', 'database');
    $post = Post::factory()->published()
        ->create();
    $post->update(['status' => PublishStatus::Draft]);

    $response = get(route('ogImage', $post));

    $response
        ->assertNotFound()
        ->assertHeaderMissing('Set-Cookie');
    assertDatabaseCount('sessions', 0);
});

it('deletes the cached OG image with its post', function () {
    $post = Post::factory()->published()
        ->create();
    Storage::disk('local')->put("og-images/{$post->id}/image.png", 'png');
    Storage::disk('local')->put("og-images/{$post->id}/signature", 'signature');

    $post->forceDelete();

    Storage::disk('local')->assertMissing("og-images/{$post->id}");
});

it('rejects cache operations for a post without a scalar key', function () {
    expect(fn () => app(OgImageCache::class)->forget(new Post))
        ->toThrow(UnexpectedValueException::class, 'without a scalar key');
});
