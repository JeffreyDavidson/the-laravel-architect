<?php

namespace App\Filament\Resources\Episodes\Tables;

use App\Enums\PublishStatus;
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
                TextColumn::make('episode_code')
                    ->label('#')
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
                TextColumn::make('formatted_duration')
                    ->label('Duration'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PublishStatus $state): string => $state->color()),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::labels(includeInReview: false)),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('episode_number', 'desc');
    }
}
