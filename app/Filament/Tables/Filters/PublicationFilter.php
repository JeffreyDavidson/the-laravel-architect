<?php

declare(strict_types=1);

namespace App\Filament\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Enums\PublicationState;

/**
 * Filters publishable content by whether it is live, scheduled or not yet published,
 * using the model's published / scheduled / unpublished query scopes.
 */
final class PublicationFilter extends SelectFilter
{
    public static function getDefaultName(): string
    {
        return 'publication';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Publication')
            ->options(PublicationState::class)
            ->query(fn (Builder $query, array $data): Builder => $this->applyState($query, $data));
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<mixed>  $data
     * @return Builder<TModel>
     */
    private function applyState(Builder $query, array $data): Builder
    {
        $state = is_string($data['value'] ?? null) ? PublicationState::tryFrom($data['value']) : null;

        if (! $state instanceof PublicationState) {
            return $query;
        }

        $query->scopes($state->scope());

        return $query;
    }
}
