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

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

it('shows the content areas that still need public details', function () {
    Project::query()->create([
        'title' => 'Needs content',
        'slug' => 'needs-content',
        'description' => 'Description',
    ]);
    Project::query()->create([
        'title' => 'Complete project',
        'slug' => 'complete-project',
        'description' => 'Description',
        'content' => 'A finished case study.',
        'featured_image_path' => 'projects/complete.webp',
    ]);

    Podcast::query()->create([
        'name' => 'Needs links',
        'slug' => 'needs-links',
        'description' => 'Description',
    ]);

    Episode::query()->create([
        'title' => 'Needs details',
        'slug' => 'needs-details',
        'description' => 'Description',
    ]);

    livewire(ContentReadinessWidget::class)
        ->assertSee('Project previews')
        ->assertSee('Project stories')
        ->assertSee('Podcast links')
        ->assertSee('Episode details')
        ->assertSee('Post content')
        ->assertSee('Newsletter issues')
        ->assertSee('Video metadata')
        ->assertSee('Review content')
        ->assertSeeHtml('href="'.ProjectResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.PodcastResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.EpisodeResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.PostResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.NewsletterIssueResource::getUrl('index').'"')
        ->assertSeeHtml('href="'.VideoResource::getUrl('index').'"');
});
