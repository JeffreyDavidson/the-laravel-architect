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

    private const int QUALITY = 82;

    public function store(UploadedFile $file, ?string $directory, string $diskName): ?string
    {
        $contents = $file->getContent();

        try {
            $image = Image::fromBytes($contents);

            if ($image->width() > self::MAX_DIMENSION || $image->height() > self::MAX_DIMENSION) {
                $image = $image->scale(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
            }

            $optimized = $image->toWebp()->quality(self::QUALITY)->toBytes();
        } catch (ImageException) {
            return null;
        }

        $path = trim(($directory ?? '').'/'.Str::ulid().'.webp', '/');

        return Storage::disk($diskName)->put($path, $optimized, 'public') ? $path : null;
    }
}
