<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Videos\VideoResource;
use App\Filament\Widgets\ContentReadinessWidget;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('includes audio-only episodes in the queue until show notes are provided', function () {
    Episode::factory()->create();

    livewire(ContentReadinessWidget::class)->assertViewHas('items', function (array $items): bool {
        foreach ($items as $item) {
            if (is_array($item) && ($item['label'] ?? null) === 'Episode details') {
                return ($item['count'] ?? null) === 1;
            }
        }

        return false;
    });
});

it('shows the content areas that still need public details', function () {
    Project::factory()->create(['content' => null]);
    Project::factory()->create(['featured_image_path' => 'projects/complete.webp']);

    Podcast::factory()->create();

    Episode::factory()->create(['transistor_url' => null]);

    livewire(ContentReadinessWidget::class)
        ->assertSee('Project previews')
        ->assertSee('Project stories')
        ->assertSee('Podcast links')
        ->assertSee('Episode details')
        ->assertDontSee('Post content')
        ->assertDontSee('Newsletter issues')
        ->assertDontSee('Video metadata')
        ->assertSeeHtml('href="'.ProjectResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.PodcastResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.EpisodeResource::getUrl('index').'"')
        ->assertDontSeeHtml('href="'.PostResource::getUrl('index').'"')
        ->assertDontSeeHtml('href="'.NewsletterIssueResource::getUrl('index').'"')
        ->assertDontSeeHtml('href="'.VideoResource::getUrl('index').'"');
});

it('renders a clear completed state when no content needs attention', function () {
    livewire(ContentReadinessWidget::class)
        ->assertSee('Everything is ready')
        ->assertSee('The public-facing content checks are complete.');
});
it('keeps the readiness widget query count bounded', function () {
    Cache::forget('filament.dashboard.content-readiness');
    DB::enableQueryLog();

    livewire(ContentReadinessWidget::class)->assertViewHas('items');

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(8);
});
