<?php

use App\Content\ProductionContentSource;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Double;

uses(RefreshDatabase::class);

test('production public content can be synchronized into staging', function (): void {
    $source = Double::for(ProductionContentSource::class);
    $source->expects('exportTo')->resolves(function (string $path): void {
        file_put_contents($path, json_encode(commandPublicContentArchiveFixture(), JSON_THROW_ON_ERROR));
    });
    $source->expects('copyMedia')->with(['posts/production.webp']);
    app()->instance(ProductionContentSource::class, $source);

    $this->artisan('content:sync-production')
        ->expectsOutputToContain('1 posts')
        ->expectsOutput('1 referenced public media files synchronized.')
        ->assertSuccessful();

    expect(Post::query()->where('slug', 'production-post')->exists())->toBeTrue();
});

test('production public content sync refuses to run in production', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    $source = Double::for(ProductionContentSource::class);
    $source->allows('exportTo')->never();
    $source->allows('copyMedia')->never();
    app()->instance(ProductionContentSource::class, $source);

    $this->artisan('content:sync-production')
        ->expectsOutputToContain('may only run in a non-production environment')
        ->assertFailed();
});

/** @return array<string, mixed> */
function commandPublicContentArchiveFixture(): array
{
    return [
        'version' => 1,
        'exported_at' => now()->toAtomString(),
        'categories' => [[
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Architecture articles',
        ]],
        'posts' => [[
            'title' => 'Production post',
            'slug' => 'production-post',
            'excerpt' => 'Production excerpt',
            'content' => 'Production content',
            'featured_image_path' => 'posts/production.webp',
            'published_at' => now()->subDay()->toAtomString(),
            'category_slug' => 'architecture',
            'tags' => [],
            'seo' => null,
        ]],
        'projects' => [],
        'podcasts' => [],
        'episodes' => [],
        'videos' => [],
        'testimonials' => [],
    ];
}
