<?php

namespace App\Filament\Resources\ContactInquiries\Schemas;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inquiry')
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('type')
                            ->formatStateUsing(fn (?string $state): ?string => $state === null
                                ? null
                                : (ContactType::tryFrom($state)?->getLabel() ?? $state))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('budget')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Not provided'),
                        TextInput::make('project_title')
                            ->label('Project')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('General inquiry'),
                        Textarea::make('message')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(8)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Follow-up')
                    ->schema([
                        Select::make('status')
                            ->options(ContactInquiryStatus::labels())
                            ->required(),
                        Textarea::make('notes')
                            ->label('Private notes')
                            ->helperText('Keep follow-up details concise. Notes are encrypted with the inquiry.')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
