<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('keeps archive filters accessible and server rendered', function () {
    $author = User::factory()->create();
    Post::query()->create([
        'title' => 'Accessible archive article',
        'slug' => 'accessible-archive-article',
        'content' => 'Published content.',
        'user_id' => $author->id,
        'status' => PublishStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $page = $this->browserPage(route('archive.index', absolute: false), 'desktop');

    $page->assertPresent('form[aria-label="Filter archive"]')
        ->assertAttribute('#archive-type', 'name', 'type')
        ->assertAttribute('#archive-year', 'name', 'year')
        ->assertSee('Accessible archive article')
        ->assertNoJavaScriptErrors();
});
