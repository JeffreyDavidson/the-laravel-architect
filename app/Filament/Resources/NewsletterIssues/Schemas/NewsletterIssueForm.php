<?php

namespace App\Filament\Resources\NewsletterIssues\Schemas;

use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Forms\Components\PublishStatusSelect;
use App\Models\NewsletterIssue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use RalphJSmit\Filament\SEO\SEO;

class NewsletterIssueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Issue Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),
                        TextInput::make('slug')
                            ->disabled(fn (?NewsletterIssue $record): bool => $record?->isSlugLocked() ?? false)
                            ->helperText('URLs stay locked after first publication, even when unpublished.')
                            ->required()
                            ->maxLength(255)
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->notIn(fn (): array => self::reservedSlugs())
                            ->validationMessages([
                                'not_in' => 'This slug is already used by another newsletter page. Choose a different slug.',
                            ])
                            ->unique(),
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

    /**
     * Slugs taken by static /newsletter/* routes, which are registered before the
     * issue route and would make an issue with the same slug unreachable.
     *
     * @return array<int, string>
     */
    private static function reservedSlugs(): array
    {
        return collect(Router::getRoutes()->getRoutes())
            ->map(fn (Route $route): string => $route->uri())
            ->filter(fn (string $uri): bool => preg_match('#\Anewsletter/[^/{]+\z#', $uri) === 1)
            ->map(fn (string $uri): string => Str::after($uri, 'newsletter/'))
            ->unique()
            ->values()
            ->all();
    }
}
