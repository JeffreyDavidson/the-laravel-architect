<?php

use App\Models\Post;
use App\Presenters\PostPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('renders a signed preview for an unpublished post', function () {
    $draft = Post::factory()->create();
    $url = PostPresenter::from($draft)->previewUrl();

    $response = get($url);

    $response->assertOk()
        ->assertSee($draft->title)
        ->assertSee('Preview mode')
        ->assertSee('noindex, nofollow');
});
