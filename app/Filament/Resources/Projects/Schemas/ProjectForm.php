<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use RalphJSmit\Filament\SEO\SEO;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                            ->unique(),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->helperText('Explain who the product helps and the problem it solves in one or two sentences.')
                            ->columnSpanFull(),
                        MarkdownEditor::make('content')
                            ->label('Full Write-up')
                            ->helperText('Tell the story under headings: The problem, My contribution, and The result. Use verified outcomes, then add implementation details and screenshots with captions. Mention when a project is still in development.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Links & Media')
                    ->schema([
                        TextInput::make('url')
                            ->label('Live URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('github_url')
                            ->label('GitHub URL')
                            ->url()
                            ->maxLength(255),
                        FileUpload::make('featured_image_path')
                            ->helperText('Upload a real product screenshot, ideally 1600 × 900 or larger. Keep its original colors; the site adds the TLA frame. Remove private data first. An image placeholder appears until you upload one.')
                            ->disk('public')
                            ->directory('projects')
                            ->image()
                            ->maxSize(10240),
                        TagsInput::make('tech_stack')
                            ->helperText('e.g. Laravel, Vue.js, Tailwind CSS'),
                    ])->columns(2),

                Section::make('Display')
                    ->schema([
                        SpatieTagsInput::make('tags'),
                        Toggle::make('is_featured')
                            ->label('Featured on homepage'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->options(ProjectStatus::labels())
                            ->default(ProjectStatus::Draft)
                            ->required(),
                    ])->columns(2),

                Section::make('SEO')
                    ->schema([
                        SEO::make(),
                    ])
                    ->collapsed(),
            ]);
    }
}
