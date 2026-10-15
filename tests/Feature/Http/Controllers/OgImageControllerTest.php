<?php

use App\Models\Post;
use App\Services\OgImageGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('generates an OG image once and serves later requests from private storage', function () {
    Storage::fake('local');

    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::type(Post::class))
        ->returns('generated-png');
    app()->instance(OgImageGenerator::class, $generator);

    $response = get(route('ogImage', $post));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Cache-Control', 'max-age=86400, public')
        ->assertContent('generated-png');
    $response = get(route('ogImage', $post));

    $response
        ->assertOk()
        ->assertContent('generated-png');

    Storage::disk('local')->assertExists([
        "og-images/{$post->id}/image.png",
        "og-images/{$post->id}/signature",
    ]);
});

it('regenerates an OG image when rendered post data changes', function () {
    Storage::fake('local');

    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    app()->instance(OgImageGenerator::class, $generator);

    $response = get(route('ogImage', $post));

    $response
        ->assertContent('first-png');

    $post->update(['title' => 'Updated title']);

    $response = get(route('ogImage', $post));

    $response
        ->assertContent('updated-png');
});

it('regenerates an OG image when its category name changes', function () {
    Storage::fake('local');

    $post = Post::factory()->published()
        ->create();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    app()->instance(OgImageGenerator::class, $generator);

    $response = get(route('ogImage', $post));

    $response
        ->assertContent('first-png');

    $post->category()
        ->firstOrFail()
        ->update(['name' => 'Updated category']);

    $response = get(route('ogImage', $post));

    $response
        ->assertContent('updated-png');
});

it('serves OG images to crawlers without starting a session or setting cookies', function () {
    Storage::fake('local');

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
    Storage::fake('local');

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
