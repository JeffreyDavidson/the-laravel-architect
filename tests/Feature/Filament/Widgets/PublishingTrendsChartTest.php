<?php

use App\Filament\Widgets\PublishingTrendsChart;
use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

afterEach(function () {
    Date::setTestNow();
});

it('plots published content by month and excludes drafts and older records', function () {
    Date::setTestNow(Carbon::parse('2026-09-15 12:00:00'));
    $administrator = User::factory()->create(['is_admin' => true]);

    Post::query()->create([
        'title' => 'September post',
        'slug' => 'september-post',
        'content' => 'Content',
        'user_id' => $administrator->id,
        'status' => 'published',
        'published_at' => '2026-09-10 12:00:00',
    ]);
    Post::query()->create([
        'title' => 'Draft post',
        'slug' => 'draft-post',
        'content' => 'Content',
        'user_id' => $administrator->id,
        'status' => 'draft',
        'published_at' => '2026-09-11 12:00:00',
    ]);
    Episode::query()->create([
        'title' => 'August episode',
        'slug' => 'august-episode',
        'description' => 'Description',
        'status' => 'published',
        'published_at' => '2026-08-20 12:00:00',
    ]);
    NewsletterIssue::query()->create([
        'title' => 'April issue',
        'slug' => 'april-issue',
        'content' => 'Content',
        'status' => 'published',
        'published_at' => '2026-04-02 12:00:00',
    ]);
    NewsletterIssue::query()->create([
        'title' => 'Older issue',
        'slug' => 'older-issue',
        'content' => 'Content',
        'status' => 'published',
        'published_at' => '2026-03-31 12:00:00',
    ]);

    $this->actingAs($administrator);

    $widget = new class extends PublishingTrendsChart
    {
        /** @return array{datasets: array<int, array{label: string, data: list<int>}>, labels: array<int, string>} */
        public function data(): array
        {
            return $this->getData();
        }
    };
    $data = $widget->data();

    expect($data['labels'])->toBe(['Apr 2026', 'May 2026', 'Jun 2026', 'Jul 2026', 'Aug 2026', 'Sep 2026'])
        ->and($data['datasets'][0])
        ->toMatchArray([
            'label' => 'Posts',
            'data' => [0, 0, 0, 0, 0, 1],
        ])
        ->and($data['datasets'][1])
        ->toMatchArray([
            'label' => 'Episodes',
            'data' => [0, 0, 0, 0, 1, 0],
        ])
        ->and($data['datasets'][2])
        ->toMatchArray([
            'label' => 'Newsletter issues',
            'data' => [1, 0, 0, 0, 0, 0],
        ]);

    livewire(PublishingTrendsChart::class)
        ->assertSee('Publishing activity')
        ->assertSee('Published content over the last six months.');
});
