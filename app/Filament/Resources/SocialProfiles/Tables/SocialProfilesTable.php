<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialProfiles\Tables;

use App\Enums\SocialPlatform;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SocialProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform')
                    ->searchable()
                    ->formatStateUsing(fn (SocialPlatform $state): string => self::platformLabel($state))
                    ->badge(),
                TextColumn::make('label')
                    ->label('Display label')
                    ->searchable()
                    ->placeholder('Platform name'),
                TextColumn::make('url')
                    ->label('Profile URL')
                    ->searchable()
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
            ->filters([
                SelectFilter::make('platform')
                    ->options(SocialPlatform::class),
                TernaryFilter::make('is_enabled')
                    ->label('Enabled'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    private static function platformLabel(SocialPlatform $platform): string
    {
        return $platform->getLabel();
    }
}
