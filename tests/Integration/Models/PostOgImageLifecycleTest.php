<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

it('deletes the cached OG image with its post', function () {
    Storage::fake('local');

    $post = Post::factory()->published()
        ->create();
    Storage::disk('local')->put("og-images/{$post->id}/image.png", 'png');
    Storage::disk('local')->put("og-images/{$post->id}/signature", 'signature');

    $post->forceDelete();

    Storage::disk('local')->assertMissing("og-images/{$post->id}");
});
