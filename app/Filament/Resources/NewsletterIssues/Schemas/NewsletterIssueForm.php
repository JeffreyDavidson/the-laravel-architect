<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Schemas;

use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Filament\Forms\Components\SlugInput;
use App\Filament\Forms\Components\SlugSourceInput;
use App\Models\NewsletterIssue;
use App\Rules\NotReservedNewsletterSlug;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use RalphJSmit\Filament\SEO\SEO;

final class NewsletterIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Issue Content')
                    ->schema([
                        SlugSourceInput::make('title')
                            ->required()
                            ->maxLength(255),
                        SlugInput::make('slug')
                            ->lockedAfterPublication()
                            ->rule(new NotReservedNewsletterSlug),
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->helperText('Short summary shown in the public archive.')
                            ->columnSpanFull(),
                        MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Publishing')
                    ->schema([
                        PublishStatusSelect::make('status'),
                        PublishDatePicker::make('published_at')
                            ->disabled(fn (?NewsletterIssue $record): bool => $record?->wasSent() ?? false)
                            ->hint(fn (?NewsletterIssue $record): ?string => $record?->wasSent() === true
                                ? 'Locked after sending, so emailed links keep working.'
                                : null),
                    ])
                    ->columns(2),
                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}
