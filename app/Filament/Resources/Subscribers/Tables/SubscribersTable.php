<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscribers\Tables;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class SubscribersTable
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
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->state(fn (Subscriber $record): SubscriberStatus => $record->status())
                    ->badge(),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(SubscriberStatus::class)
                    ->default(SubscriberStatus::Active)
                    ->query(function (Builder $query, array $data): void {
                        self::applyStatusFilter($query, $data);
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Subscriber $record): bool => ! $record->isSuppressed(),
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize('deleteAny'),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading('No subscribers yet')
            ->emptyStateDescription('Confirmed newsletter subscribers will appear here.');
    }

    /**
     * @param  Builder<Subscriber>  $query
     * @param  array<mixed>  $data
     */
    private static function applyStatusFilter(Builder $query, array $data): void
    {
        $status = $data['value'] ?? null;

        if (is_string($status)) {
            $status = SubscriberStatus::tryFrom($status);
        }

        if (! $status instanceof SubscriberStatus) {
            return;
        }

        $query->withStatus($status);
    }
}
