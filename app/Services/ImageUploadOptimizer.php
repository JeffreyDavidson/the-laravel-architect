<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadOptimizer
{
    public const int MAX_DIMENSION = 1600;

    public const int MAX_FILE_SIZE_KB = 10240;

    public const string UPLOAD_HELPER_TEXT = 'Images are converted to WebP and resized to a maximum of 1600 px per side. Maximum upload size: 10 MB.';

    private const int QUALITY = 82;

    public function store(UploadedFile $file, ?string $directory, string $diskName): ?string
    {
        return $this->storeContents($file->getContent(), $directory, $diskName);
    }

    public function storeContents(string $contents, ?string $directory, string $diskName): ?string
    {
        $optimized = $this->optimize($contents);

        if ($optimized === null) {
            return null;
        }

        return $this->storeOptimizedContents($optimized, $directory, $diskName);
    }

    public function storeOptimizedContents(string $contents, ?string $directory, string $diskName): ?string
    {
        $path = trim(($directory ?? '').'/'.Str::ulid().'.webp', '/');

        return Storage::disk($diskName)->put($path, $contents, 'public') ? $path : null;
    }

    public function optimize(string $contents): ?string
    {

        try {
            $image = Image::fromBytes($contents);

            if ($image->width() > self::MAX_DIMENSION || $image->height() > self::MAX_DIMENSION) {
                $image = $image->scale(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
            }

            $optimized = $image->toWebp()
                ->quality(self::QUALITY)
                ->toBytes();
        } catch (ImageException) {
            return null;
        }

        return $optimized;
    }
}
