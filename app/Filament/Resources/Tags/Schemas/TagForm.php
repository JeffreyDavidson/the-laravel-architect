<?php

namespace App\Filament\Resources\Tags\Schemas;

use App\Models\Tag;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_array($state)) {
                            $state = $state[app()->getLocale()] ?? '';
                        }

                        return is_string($state) ? $state : '';
                    })
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug($state ?? ''));
                        }
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
                    ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                    ->rule(fn (?Tag $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                        if (! is_string($value) || preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value) !== 1) {
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
