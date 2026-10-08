<?php

use App\Filament\Widgets\PublishingTrendsChart;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Js;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

it('plots published content by month and excludes drafts and older records', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $administrator = User::factory()->create(['is_admin' => true]);

    Post::factory()
        ->published()
        ->create(['published_at' => '2026-09-10 12:00:00']);
    Post::factory()->create(['published_at' => '2026-09-11 12:00:00']);
    Episode::factory()
        ->published()
        ->create(['published_at' => '2026-08-20 12:00:00']);
    NewsletterIssue::factory()
        ->published()
        ->create(['published_at' => '2026-04-02 12:00:00']);
    NewsletterIssue::factory()
        ->published()
        ->create(['published_at' => '2026-03-31 12:00:00']);

    actingAs($administrator);

    $chartData = Js::from([
        'datasets' => [
            ['label' => 'Posts', 'data' => [0, 0, 0, 0, 0, 1]],
            ['label' => 'Episodes', 'data' => [0, 0, 0, 0, 1, 0]],
            ['label' => 'Newsletter issues', 'data' => [1, 0, 0, 0, 0, 0]],
        ],
        'labels' => ['Apr 2026', 'May 2026', 'Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026'],
    ]);

    livewire(PublishingTrendsChart::class)
        ->assertSee('Publishing activity')
        ->assertSee('Published content over the last six months.')
        ->assertSeeHtml("cachedData: {$chartData->toHtml()}");
});
