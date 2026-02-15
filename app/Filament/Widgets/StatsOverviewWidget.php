<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
use App\Models\Project;
use App\Models\Subscriber;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Published Posts', Post::where('status', 'published')->count())
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary'),
            Stat::make('Podcast Episodes', Episode::where('status', 'published')->count())
                ->icon(Heroicon::OutlinedMusicalNote)
                ->color('success'),
            Stat::make('Projects', Project::where('status', 'published')->count())
                ->icon(Heroicon::OutlinedCodeBracket)
                ->color('info'),
            Stat::make('Subscribers', Subscriber::whereNull('unsubscribed_at')->count())
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('warning'),
        ];
    }
}
