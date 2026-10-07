<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Services\ImageUploadOptimizer;
use Closure;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class OptimizedImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->image()
            ->maxSize(ImageUploadOptimizer::MAX_FILE_SIZE_KB)
            ->rule(static fn (ImageUploadOptimizer $optimizer): Closure => static function (string $attribute, mixed $value, Closure $fail) use ($optimizer): void {
                if ($value instanceof UploadedFile && $optimizer->exceedsPixelLimit($value->getContent())) {
                    $fail(ImageUploadOptimizer::PIXEL_LIMIT_MESSAGE);
                }
            })
            ->helperText(ImageUploadOptimizer::UPLOAD_HELPER_TEXT)
            ->automaticallyResizeImagesToWidth((string) ImageUploadOptimizer::MAX_DIMENSION)
            ->automaticallyResizeImagesToHeight((string) ImageUploadOptimizer::MAX_DIMENSION)
            ->automaticallyResizeImagesMode('contain')
            ->automaticallyUpscaleImagesWhenResizing(false)
            ->saveUploadedFileUsing(
                fn (BaseFileUpload $component, TemporaryUploadedFile $file, ImageUploadOptimizer $optimizer): ?string => $optimizer->store($file, $component->getDirectory(), $component->getDiskName()),
            );
    }
}
