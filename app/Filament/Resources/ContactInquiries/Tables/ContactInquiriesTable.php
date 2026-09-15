<?php

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('From')
                    ->limit(32),
                TextColumn::make('email')
                    ->limit(36)
                    ->copyable(),
                TextColumn::make('type')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => ContactType::tryFrom($state)?->getLabel() ?? $state),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ContactInquiryStatus $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContactInquiryStatus::labels()),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorize('deleteAny'),
                ]),
            ]);
    }
}
