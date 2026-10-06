<?php

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishableFixtures;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

// Frozen so the review dates and the cutoff share one day, even across midnight.
beforeEach(function () {
    freezeSecond();
    actingAs(User::factory()->create(['is_admin' => true]));
});

it('saves the official source with its review date', function () {
    $post = PublishableFixtures::readyPost();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'source_url' => 'https://laravel.com/docs/queues',
            'last_reviewed_at' => today()->toDateString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();
    $lastReviewedAt = $post->getAttribute('last_reviewed_at');

    expect($post->getAttribute('source_url'))
        ->toBe('https://laravel.com/docs/queues')
        ->and($lastReviewedAt instanceof CarbonInterface && $lastReviewedAt->isToday())
        ->toBeTrue();
});

it('requires the source and review date together', function (array $data, string $missing) {
    $post = PublishableFixtures::readyPost();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm($data)
        ->call('save')
        ->assertHasFormErrors([$missing => 'required']);
})->with([
    'source without a review date' => [['source_url' => 'https://laravel.com/docs'], 'last_reviewed_at'],
    'review date without a source' => [['last_reviewed_at' => '2026-09-01'], 'source_url'],
]);

it('filters the posts table to sources due for review', function () {
    $due = PublishableFixtures::readyPost(['source_url' => 'https://laravel.com/docs']);
    $current = PublishableFixtures::readyPost([
        'title' => 'Current post',
        'slug' => 'current-post',
        'source_url' => 'https://laravel.com/docs',
        'last_reviewed_at' => today(),
    ]);

    livewire(ListPosts::class)
        ->filterTable('review_due')
        ->assertCanSeeTableRecords([$due])
        ->assertCanNotSeeTableRecords([$current])
        ->assertTableColumnExists('source_review_status');
});
