<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Project;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class ContentReadinessWidget extends Widget
{
    #[\Override]
    protected string $view = 'filament.widgets.content-readiness-widget';

    #[\Override]
    protected int|string|array $columnSpan = 'full';

    #[\Override]
    protected static ?int $sort = -2;

    /**
     * @return array{items: list<array{label: string, description: string, count: int, url: string}>}
     */
    protected function getViewData(): array
    {
        return [
            'items' => [
                [
                    'label' => 'Project previews',
                    'description' => 'Add an optimized featured image to each project.',
                    'count' => Project::query()->where(fn (Builder $query): Builder => $query
                        ->whereNull('featured_image_path')
                        ->orWhere('featured_image_path', '')
                    )->count(),
                    'url' => ProjectResource::getUrl('index'),
                ],
                [
                    'label' => 'Project stories',
                    'description' => 'Finish the case study for each project.',
                    'count' => Project::query()->where(fn (Builder $query): Builder => $query
                        ->whereNull('content')
                        ->orWhere('content', '')
                    )->count(),
                    'url' => ProjectResource::getUrl('index'),
                ],
                [
                    'label' => 'Podcast links',
                    'description' => 'Add at least one place listeners can subscribe.',
                    'count' => Podcast::query()
                        ->active()
                        ->whereNull('apple_url')
                        ->whereNull('spotify_url')
                        ->whereNull('rss_url')
                        ->whereNull('youtube_url')
                        ->count(),
                    'url' => PodcastResource::getUrl('index'),
                ],
                [
                    'label' => 'Episode details',
                    'description' => 'Add show notes, audio, a transcript, or guest details.',
                    'count' => Episode::query()
                        ->whereNull('show_notes')
                        ->whereNull('transcript')
                        ->whereNull('audio_url')
                        ->whereNull('audio_path')
                        ->whereNull('embed_url')
                        ->whereNull('youtube_url')
                        ->whereNull('guest_name')
                        ->doesntHave('tags')
                        ->count(),
                    'url' => EpisodeResource::getUrl('index'),
                ],
            ],
        ];
    }
}
