<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterIssues\Schemas;

use App\Enums\NewsletterTemplate;
use App\Filament\Forms\Components\OptimizedImageUpload;
use App\Models\NewsletterIssue;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

final class NewsletterTemplateForm
{
    /** @return list<Section> */
    public static function fields(): array
    {
        return [
            Section::make('Email layout')
                ->key('newsletterTemplate')
                ->schema([
                    Select::make('email_template')
                        ->label('Template')
                        ->options(NewsletterTemplate::class)
                        ->default(NewsletterTemplate::NewArticle)
                        ->placeholder('Original layout')
                        ->rules([Rule::enum(NewsletterTemplate::class)])
                        ->live(),
                    Textarea::make('email_deck')
                        ->label('Display introduction')
                        ->rows(2)
                        ->maxLength(500)
                        ->helperText('Visible below the heading. The excerpt remains the inbox preview.')
                        ->columnSpanFull(),
                    Section::make('Article link and image')
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => in_array($get('email_template'), [NewsletterTemplate::NewArticle, NewsletterTemplate::NewArticle->value], true))
                        ->schema([
                            TextInput::make('email_options.article_url')
                                ->label('Article URL')
                                ->rules(['nullable', 'url:http,https'])
                                ->maxLength(2048),
                            TextInput::make('email_options.button_label')
                                ->label('Button label')
                                ->maxLength(100)
                                ->default('Read the article'),
                            TextInput::make('email_options.topic')
                                ->label('Topic')
                                ->maxLength(80),
                            OptimizedImageUpload::make('email_options.hero_path')
                                ->label('Article image')
                                ->columnSpanFull()
                                ->disk('public')
                                ->directory('newsletter')
                                ->visibility('public')
                                ->preventFilePathTampering(allowFilePathUsing: function (string $file, ?NewsletterIssue $record): bool {
                                    // Filament's native original-path check does not traverse this JSON attribute.
                                    $options = $record?->getOriginal('email_options');

                                    return is_array($options) && ($options['hero_path'] ?? null) === $file;
                                }),
                            TextInput::make('email_options.hero_alt')
                                ->label('Image description')
                                ->columnSpanFull()
                                ->required(fn (Get $get): bool => filled($get('email_options.hero_path')))
                                ->maxLength(500),
                        ])
                        ->columns(2),
                ])
                ->headerActions([
                    Action::make('useStarterContent')
                        ->label('Use starter content')
                        ->authorize(function (?Model $record): bool {
                            $user = auth()->user();

                            return $user?->can($record instanceof Model ? 'update' : 'create', $record ?? NewsletterIssue::class) ?? false;
                        })
                        ->requiresConfirmation()
                        ->modalDescription('Replace the current body with the selected template outline?')
                        ->action(function (Get $get, Set $set): void {
                            $value = $get('email_template');
                            $template = $value instanceof NewsletterTemplate ? $value : (is_string($value) ? NewsletterTemplate::tryFrom($value) : null);

                            if ($template === null) {
                                return;
                            }

                            $set('content', file_get_contents(resource_path("newsletter/{$template->value}.md")) ?: '');
                        }),
                ])
                ->columns(2),
        ];
    }

    /** @return list<Action> */
    public static function editActions(EditRecord $page): array
    {
        return [
            Action::make('previewEmail')
                ->label('Preview email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->authorize('update')
                ->action(function () use ($page): void {
                    $page->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $page->redirect(route('preview.newsletterEmail', ['newsletterIssue' => $page->getRecord()]));
                }),
        ];
    }
}
