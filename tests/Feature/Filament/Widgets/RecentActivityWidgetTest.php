<?php

use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('renders an empty state when there is no recent activity', function () {
    livewire(RecentActivityWidget::class)
        ->assertSee('No activity yet');
});

/**
 * Create posts and pin each one's last edit time.
 *
 * @param  list<array{string, PublishStatus, string}>  $posts
 */
function postsEditedAt(array $posts): void
{
    foreach ($posts as [$title, $status, $updatedAt]) {
        $post = Post::factory()->create([
            'title' => $title,
            'status' => $status,
        ]);

        $post->forceFill(['updated_at' => Date::parse($updatedAt)])
            ->saveQuietly();
    }
}

it('lists the most recently edited posts newest first with their status', function () {
    // Arrange: titles avoid status words, so the labels can only come from the status
    postsEditedAt([
        ['Oldest edit', PublishStatus::Draft, '2026-08-19 09:00:00'],
        ['Second newest edit', PublishStatus::Published, '2026-08-19 13:00:00'],
        ['Third newest edit', PublishStatus::InReview, '2026-08-19 11:00:00'],
        ['Newest edit', PublishStatus::Draft, '2026-08-19 15:00:00'],
    ]);

    // Act
    $component = livewire(RecentActivityWidget::class);

    // Assert
    $component->assertViewHas('activities', function (Collection $activities): bool {
        $labels = $activities->pluck('label');
        $statuses = $activities->pluck('status');

        return $labels->all() === ['Newest edit', 'Second newest edit', 'Third newest edit', 'Oldest edit']
            && $statuses->all() === ['Draft', 'Published', 'In Review', 'Draft'];
    });
});

it('shows only the five most recent edits', function () {
    // Arrange
    postsEditedAt(array_map(
        fn (int $hour): array => ["Edit at {$hour}", PublishStatus::Draft, sprintf('2026-08-19 %02d:00:00', $hour)],
        range(9, 14),
    ));

    // Act
    $component = livewire(RecentActivityWidget::class);

    // Assert
    $component->assertViewHas('activities', function (Collection $activities): bool {
        $labels = $activities->pluck('label');

        return $labels->all() === ['Edit at 14', 'Edit at 13', 'Edit at 12', 'Edit at 11', 'Edit at 10'];
    });
});
