<?php

namespace App\Filament\Resources\Episodes\Tables;

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Presenters\EpisodePresenter;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EpisodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('episode_number')
                    ->label('#')
                    ->state(fn (Episode $record): string => EpisodePresenter::from($record)->code())
                    ->sortable(['season_number', 'episode_number']),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('podcast.name')
                    ->label('Podcast')
                    ->sortable(),
                TextColumn::make('guest_name')
                    ->label('Guest')
                    ->placeholder('Solo'),
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->state(fn (Episode $record): string => EpisodePresenter::from($record)->duration()),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PublishStatus $state): string => $state->color()),
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::labels(includeInReview: false)),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('episode_number', 'desc');
    }
}
