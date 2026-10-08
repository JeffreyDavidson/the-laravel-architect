<?php

use App\Models\Post;
use App\ViewModels\ArchiveViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StructuredDataExpectations as Schema;

pest()->use(RefreshDatabase::class);

it('offsets a paginated archive listing', function () {
    Schema::useFixedOrigin();

    foreach (range(1, 19) as $day) {
        Post::factory()
            ->published()
            ->create(['title' => "Post {$day}", 'slug' => "post-{$day}", 'published_at' => now()->subDays($day)]);
    }

    request()->query->set('page', 2);

    $data = app(ArchiveViewModel::class)
        ->data();

    expect(Schema::graph($data['pageMeta']))->toBe([
        Schema::website(),
        ...Schema::collection('Archive', 'https://example.test/archive?page=2', [
            19 => ['Post 19', 'https://example.test/blog/post-19'],
        ]),
        Schema::breadcrumbs([
            ['Home', 'https://example.test'],
            ['Archive', 'https://example.test/archive?page=2'],
        ]),
    ]);
});
