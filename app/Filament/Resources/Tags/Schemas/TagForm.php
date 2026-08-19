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
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->required()
                    ->readOnly()
                    ->formatStateUsing(function (mixed $state): string {
                        if (is_array($state)) {
                            $state = $state[app()->getLocale()] ?? '';
                        }

                        return is_string($state) ? $state : '';
                    })
                    ->maxLength(255)
                    ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                    ->rule(function (Get $get, ?Tag $record): Closure {
                        return function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            $name = $get('name');

                            if (! is_string($name)) {
                                $fail('The name must generate a valid slug of 255 characters or fewer.');

                                return;
                            }

                            $slug = Str::slug($name);

                            if ($slug === '' || mb_strlen($slug) > 255) {
                                $fail('The name must generate a valid slug of 255 characters or fewer.');

                                return;
                            }

                            $query = Tag::query()->where('slug->'.app()->getLocale(), $slug);

                            if ($record !== null) {
                                $query->whereKeyNot($record->getKey());
                            }

                            if ($query->exists()) {
                                $fail('The slug generated from this name has already been taken.');
                            }
                        };
                    }),
                TextInput::make('type')
                    ->nullable(),
                TextInput::make('order_column')
                    ->numeric()
                    ->nullable(),
            ]);
    }
}
