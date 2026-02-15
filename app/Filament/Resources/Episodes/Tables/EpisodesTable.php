<?php

namespace App\Filament\Resources\Episodes\Tables;

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
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                    }),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                    ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('episode_number', 'desc');
    }
}
