<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Schemas;

use App\Enums\ContactBudget;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Models\ContactInquiry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ContactInquiryForm
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
                        Select::make('type')
                            ->options(ContactType::class)
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('budget')
                            ->options(ContactBudget::class)
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
                Section::make('Email delivery')
                    ->schema([
                        DateTimePicker::make('notification_sent_at')
                            ->label('Owner notification sent')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(fn (ContactInquiry $record): string => $record->email_attempted_at === null ? 'Queued' : 'Not sent'),
                        DateTimePicker::make('confirmation_sent_at')
                            ->label('Sender confirmation sent')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(fn (ContactInquiry $record): string => $record->email_attempted_at === null ? 'Queued' : 'Not sent'),
                    ])
                    ->columns(2),
                Section::make('Follow-up')
                    ->schema([
                        Select::make('status')
                            ->options(ContactInquiryStatus::class)
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
