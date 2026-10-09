<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Enums\ContactType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;

final class ContactInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Name and email are encrypted at rest, so SQL search can never match them.
                TextColumn::make('name')
                    ->label('From')
                    ->limit(32),
                TextColumn::make('email')
                    ->limit(36)
                    ->copyable(),
                TextColumn::make('type')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereIn('type', self::typesWithLabelMatching($search))),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ContactInquiryStatus::class),
            ])
            ->searchPlaceholder('Search by inquiry type')
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->authorize('deleteAny'),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->emptyStateHeading('No contact inquiries')
            ->emptyStateDescription('New messages from the public contact form will appear here.');
    }

    /**
     * Match the search against the type labels shown in the table, not the stored values.
     *
     * @return array<int, string>
     */
    private static function typesWithLabelMatching(string $search): array
    {
        return collect(ContactType::cases())
            ->filter(fn (ContactType $type): bool => Str::contains($type->getLabel(), $search, ignoreCase: true))
            ->map(fn (ContactType $type): string => $type->value)
            ->all();
    }
}
