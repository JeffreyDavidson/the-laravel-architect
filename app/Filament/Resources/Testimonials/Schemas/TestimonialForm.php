<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Testimonial')
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('role'),
                    TextInput::make('company'),
                    Textarea::make('body')
                        ->required()
                        ->rows(4),
                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending')
                        ->required(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }
}
