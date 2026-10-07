<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use App\Models\Tag;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SlugSourceInput::make('name')
                    ->required()
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_array($state)) {
                            $state = $state[app()->getLocale()] ?? '';
                        }

                        return is_string($state) ? $state : '';
                    }),
                TextInput::make('slug')
                    ->required()
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_array($state)) {
                            $state = $state[app()->getLocale()] ?? '';
                        }

                        return is_string($state) ? $state : '';
                    })
                    ->maxLength(255)
                    ->regex(SlugInput::PATTERN)
                    ->rule(fn (?Tag $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                        if (! is_string($value) || preg_match(SlugInput::PATTERN, $value) !== 1) {
                            $fail('The slug must contain only lowercase letters, numbers, and single hyphens.');

                            return;
                        }

                        $query = Tag::query()->where('slug->'.app()->getLocale(), $value);

                        if ($record instanceof Tag) {
                            $query->whereKeyNot($record->getKey());
                        }

                        if ($query->exists()) {
                            $fail('The slug has already been taken.');
                        }
                    }),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('order_column')
                    ->numeric()
                    ->nullable(),
            ]);
    }
}
