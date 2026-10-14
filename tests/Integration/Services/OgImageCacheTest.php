<?php

use App\Models\Post;
use App\Services\OgImageCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

it('rejects cache operations for a post without a scalar key', function () {
    Storage::fake('local');

    expect(fn () => app(OgImageCache::class)->forget(new Post))
        ->toThrow(UnexpectedValueException::class, 'without a scalar key');
});
