<?php

namespace App\Filament\Resources\Subscribers\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('subscribed_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->label('Subscribed'),
                TextColumn::make('unsubscribed_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->label('Unsubscribed')
                    ->placeholder('Active'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->state(fn ($record) => is_null($record->unsubscribed_at))
                    ->boolean(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                Filter::make('active')
                    ->label('Active only')
                    ->query(fn (Builder $query) => $query->whereNull('unsubscribed_at'))
                    ->default(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
