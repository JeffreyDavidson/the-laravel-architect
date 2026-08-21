<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Models\Subscriber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                    ->state(fn (Subscriber $record): bool => is_null($record->unsubscribed_at))
                    ->boolean(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                Filter::make('active')
                    ->label('Active only')
                    ->query(fn (Builder $query) => $query->whereNull('unsubscribed_at'))
                    ->default(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
