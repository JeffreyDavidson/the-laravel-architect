<?php

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('shows the confirmed page without a token, without indexing and without subscriber data', function () {
    Subscriber::query()->create([
        'email' => 'reader@example.com',
        'subscribed_at' => now(),
        'verified_at' => now(),
    ]);

    get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertSeeText('You’re confirmed')
        ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">')
        ->assertSee(route('blog.index'))
        ->assertDontSee('reader@example.com');
});
