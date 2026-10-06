<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
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
    $post = createPublishedPost();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->with(Argument::type(Post::class))
        ->returns('generated-png');
    $this->app->instance(OgImageGenerator::class, $generator);

    $this->get(route('og-image', $post))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertContent('generated-png');
    $this->get(route('og-image', $post))
        ->assertOk()
        ->assertContent('generated-png');

    Storage::disk('local')->assertExists([
        "og-images/{$post->id}/image.png",
        "og-images/{$post->id}/signature",
    ]);
});

it('regenerates an OG image when rendered post data changes', function () {
    $post = createPublishedPost();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    $this->app->instance(OgImageGenerator::class, $generator);

    $this->get(route('og-image', $post))
        ->assertContent('first-png');

    $post->update(['title' => 'Updated title']);

    $this->get(route('og-image', $post))
        ->assertContent('updated-png');
});

it('regenerates an OG image when its category name changes', function () {
    $post = createPublishedPost();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->times(2)
        ->returns('first-png', 'updated-png');
    $this->app->instance(OgImageGenerator::class, $generator);

    $this->get(route('og-image', $post))
        ->assertContent('first-png');

    $post->category()
        ->firstOrFail()
        ->update(['name' => 'Updated category']);

    $this->get(route('og-image', $post))
        ->assertContent('updated-png');
});

it('serves OG images to crawlers without starting a session or setting cookies', function () {
    config()->set('session.driver', 'database');
    $post = createPublishedPost();
    $generator = Double::for(OgImageGenerator::class);
    $generator->expects('generate')
        ->returns('generated-png');
    app()->instance(OgImageGenerator::class, $generator);

    $response = get(route('og-image', $post));

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
    $post = createPublishedPost();
    $post->update(['status' => PublishStatus::Draft]);

    $response = get(route('og-image', $post));

    $response
        ->assertNotFound()
        ->assertHeaderMissing('Set-Cookie');
    assertDatabaseCount('sessions', 0);
});

it('deletes the cached OG image with its post', function () {
    $post = createPublishedPost();
    Storage::disk('local')->put("og-images/{$post->id}/image.png", 'png');
    Storage::disk('local')->put("og-images/{$post->id}/signature", 'signature');

    $post->forceDelete();

    Storage::disk('local')->assertMissing("og-images/{$post->id}");
});

it('rejects cache operations for a post without a scalar key', function () {
    expect(fn () => app(OgImageCache::class)->forget(new Post))
        ->toThrow(UnexpectedValueException::class, 'without a scalar key');
});

function createPublishedPost(): Post
{
    $category = Category::query()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    return Post::query()->create([
        'title' => 'Cached OG image',
        'slug' => 'cached-og-image',
        'content' => 'Content',
        'category_id' => $category->id,
        'user_id' => User::factory()->create()
            ->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);
}
