<?php

namespace App\Filament\Forms\Components;

use App\Services\ImageUploadOptimizer;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class OptimizedImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->image()
            ->automaticallyResizeImagesToWidth((string) ImageUploadOptimizer::MAX_DIMENSION)
            ->automaticallyResizeImagesToHeight((string) ImageUploadOptimizer::MAX_DIMENSION)
            ->automaticallyResizeImagesMode('contain')
            ->automaticallyUpscaleImagesWhenResizing(false)
            ->saveUploadedFileUsing(
                fn (BaseFileUpload $component, TemporaryUploadedFile $file, ImageUploadOptimizer $optimizer): ?string => $optimizer->store($file, $component->getDirectory(), $component->getDiskName())
                    ?? $component->saveUploadedFile($file),
            );
    }
}
