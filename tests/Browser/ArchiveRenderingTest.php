<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

it('keeps archive filters accessible and server rendered', function () {
    Post::factory()
        ->published()
        ->create(['title' => 'Accessible archive article']);

    $page = $this->browserPage(route('archive.index', absolute: false), 'desktop');

    $page->assertPresent('form[aria-label="Filter archive"]')
        ->assertAttribute('#archive-type', 'name', 'type')
        ->assertAttribute('#archive-year', 'name', 'year')
        ->assertSee('Accessible archive article')
        ->assertNoJavaScriptErrors();
});
