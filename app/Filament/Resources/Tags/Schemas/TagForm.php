<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tags\Schemas;

use App\Models\Tag;
use App\Rules\UniqueTagSlug;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugInput;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugSourceInput;

final class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SlugSourceInput::make('name')
                    ->required()
                    ->formatStateUsing(self::currentLocaleValue(...)),
                TextInput::make('slug')
                    ->required()
                    ->formatStateUsing(self::currentLocaleValue(...))
                    ->maxLength(255)
                    ->regex(SlugInput::PATTERN)
                    ->rule(fn (?Tag $record): UniqueTagSlug => new UniqueTagSlug($record)),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('order_column')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    /** Tag names and slugs are translatable, so the form edits the current locale's value. */
    private static function currentLocaleValue(mixed $state): string
    {
        if (is_array($state)) {
            $state = $state[app()->getLocale()] ?? '';
        }

        return is_string($state) ? $state : '';
    }
}
