<?php

use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('renders an empty state when there is no recent activity', function () {
    livewire(RecentActivityWidget::class)
        ->assertSee('No activity yet');
});

it('renders the newest posts in chronological order', function () {
    $posts = [
        ['Old post', PublishStatus::Draft, '2026-08-19 09:00:00'],
        ['Published post', PublishStatus::Published, '2026-08-19 13:00:00'],
        ['Review post', PublishStatus::InReview, '2026-08-19 11:00:00'],
        ['Draft post', PublishStatus::Draft, '2026-08-19 15:00:00'],
    ];

    foreach ($posts as [$title, $status, $updatedAt]) {
        $post = Post::factory()->create([
            'title' => $title,
            'status' => $status,
        ]);

        $post->forceFill(['updated_at' => Date::parse($updatedAt)])
            ->saveQuietly();
    }

    livewire(RecentActivityWidget::class)
        ->assertSeeInOrder([
            'Draft post',
            'Published post',
            'Review post',
        ])
        ->assertSee('Published')
        ->assertSee('Draft');
});
