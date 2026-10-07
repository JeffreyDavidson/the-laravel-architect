<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ImageUploadOptimizer
{
    public const int MAX_DIMENSION = 1600;

    public const int MAX_FILE_SIZE_KB = 10240;

    /**
     * GD holds a decoded image at about 4 bytes per pixel. Measured through a decode, scale and
     * WebP encode, a 20 megapixel image peaks at about 90 MB, which stays under PHP's common
     * 128 MB memory limit and is affordable on the shared 1 GB server (40 megapixels peaks at
     * about 170 MB). Without a cap, a 10 MB upload can declare far more pixels and expand to
     * several hundred megabytes, exhausting PHP's memory limit or the server.
     */
    public const int MAX_PIXELS = 20_000_000;

    public const string UPLOAD_HELPER_TEXT = 'Images are converted to WebP and resized to a maximum of 1600 px per side. Maximum upload size: 10 MB and 20 megapixels.';

    public const string PIXEL_LIMIT_MESSAGE = 'This image is too large to process. Images can be at most 20 megapixels (5000 × 4000 px, for example). Resize it and upload it again.';

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

    /**
     * Whether the image header declares more than MAX_PIXELS. The image's width() and height()
     * come from getimagesizefromstring(), which reads the header without decoding any pixels.
     * Contents that are not a readable image are left to the upload's image validation.
     */
    public function exceedsPixelLimit(string $contents): bool
    {
        try {
            $image = Image::fromBytes($contents);

            return $image->width() * $image->height() > self::MAX_PIXELS;
        } catch (ImageException) {
            return false;
        }
    }

    /**
     * Convert the image to WebP within MAX_DIMENSION. Returns null when it cannot be decoded, or
     * when it declares more than MAX_PIXELS, which is refused before any pixels are decoded.
     */
    public function optimize(string $contents): ?string
    {
        if ($this->exceedsPixelLimit($contents)) {
            return null;
        }

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
