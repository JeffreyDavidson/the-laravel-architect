<?php

use App\Models\Post;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

it('shows the confirmed page without a token, without indexing and without subscriber data', function () {
    Subscriber::factory()->create([
        'email' => 'reader@example.com',
    ]);

    get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertSeeText('You’re confirmed')
        ->assertSeeHtml('<meta name="robots" content="noindex, nofollow">')
        ->assertSee(route('blog.index'))
        ->assertDontSee('reader@example.com');
});

it('shows the three newest published posts on the confirmed page', function () {
    foreach ([1 => 'Newest Post', 2 => 'Second Post', 3 => 'Third Post', 4 => 'Oldest Post'] as $daysAgo => $title) {
        Post::factory()->published()
            ->create([
                'title' => $title,
                'published_at' => now()->subDays($daysAgo),
            ]);
    }

    Post::factory()->create([
        'title' => 'Draft Post',
    ]);

    get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertSeeText('While you’re here')
        ->assertSeeTextInOrder(['Newest Post', 'Second Post', 'Third Post'])
        ->assertDontSeeText('Oldest Post')
        ->assertDontSeeText('Draft Post');
});

it('leaves out the latest posts section when nothing is published', function () {
    get(route('newsletter.confirmed'))
        ->assertOk()
        ->assertDontSeeText('While you’re here');
});
