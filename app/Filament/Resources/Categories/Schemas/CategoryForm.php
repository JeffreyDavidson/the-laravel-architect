<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use JeffreyDavidson\CreatorKit\Filament\Forms\Components\SlugInput;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                SlugInput::make('slug'),
                Textarea::make('description')
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
