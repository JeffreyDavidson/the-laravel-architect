<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->helperText('Short description for project cards')
                            ->columnSpanFull(),
                        MarkdownEditor::make('content')
                            ->label('Full Write-up')
                            ->columnSpanFull(),
                    ]),

                Section::make('Links & Media')
                    ->schema([
                        TextInput::make('url')
                            ->label('Live URL')
                            ->url(),
                        TextInput::make('github_url')
                            ->label('GitHub URL')
                            ->url(),
                        FileUpload::make('featured_image')
                            ->image()
                            ->directory('projects'),
                        TagsInput::make('tech_stack')
                            ->helperText('e.g. Laravel, Vue.js, Tailwind CSS'),
                    ])->columns(2),

                Section::make('Display')
                    ->schema([
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_featured')
                            ->label('Featured on homepage'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
