<?php

namespace App\Filament\Resources\SocialProfiles\Tables;

use App\Enums\SocialPlatform;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SocialProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform')
                    ->formatStateUsing(fn (SocialPlatform $state): string => $state->getLabel())
                    ->badge(),
                TextColumn::make('label')
                    ->label('Display label')
                    ->placeholder('Platform name'),
                TextColumn::make('url')
                    ->label('Profile URL')
                    ->url(fn (string $state): string => $state, shouldOpenInNewTab: true)
                    ->limit(48),
                ToggleColumn::make('is_enabled')
                    ->label('Enabled'),
                ToggleColumn::make('show_in_footer')
                    ->label('Footer'),
                ToggleColumn::make('show_on_contact')
                    ->label('Contact'),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }
}
