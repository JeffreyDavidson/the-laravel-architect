<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

/**
 * Permalink slug field: required, lowercase kebab-case and unique for the form's model
 * (Filament ignores the record being edited).
 */
final class SlugInput extends TextInput
{
    /** Lowercase letters and numbers separated by single hyphens. */
    public const string PATTERN = '/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/';

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->required()
            ->maxLength(255)
            ->regex(self::PATTERN)
            ->unique();
    }

    /** Disable the field once the record's slug is locked, because public URLs have no redirects. */
    public function lockedAfterPublication(): static
    {
        $this
            ->disabled(fn (?Model $record): bool => $record instanceof Model
                && method_exists($record, 'isSlugLocked')
                && $record->isSlugLocked())
            ->helperText('URLs stay locked after first publication, even when unpublished.');

        return $this;
    }
}
