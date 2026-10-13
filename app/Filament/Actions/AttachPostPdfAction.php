<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class AttachPostPdfAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'attachPdf';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Attach PDF')
            ->icon(Heroicon::OutlinedPaperClip)
            ->authorize(fn (?Post $record): bool => Gate::allows($record instanceof Post ? 'update' : 'create', $record ?? Post::class))
            ->modalHeading('Attach a PDF')
            ->modalDescription('Adds a download link at the end of the content. Move it where you want, then save the post. Uploaded PDFs are public.')
            ->modalSubmitActionLabel('Insert download link')
            ->schema([
                FileUpload::make('pdf')
                    ->label('PDF')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('attachments/posts')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->helperText('PDF files up to 10 MB.')
                    ->preventFilePathTampering()
                    ->required(),
                TextInput::make('label')
                    ->label('Link text')
                    ->default('Download the companion PDF')
                    ->maxLength(160)
                    ->regex('/\A[^\r\n]+\z/u')
                    ->required(),
            ])
            ->action(function (array $data, MarkdownEditor $schemaComponent): void {
                /** @var array{pdf: string, label: string} $data */
                $url = Storage::disk('public')->url($data['pdf']);
                $label = str_replace(['\\', '[', ']'], ['\\\\', '\\[', '\\]'], $data['label']);
                $content = $schemaComponent->getState();
                $content = is_string($content) ? $content : '';
                $schemaComponent->state($content.($content !== '' ? "\n\n" : '')."[{$label}]({$url})");
            });
    }
}
