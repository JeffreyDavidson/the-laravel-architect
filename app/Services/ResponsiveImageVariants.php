<?php

namespace App\Services;

use Illuminate\Image\ImageException;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ResponsiveImageVariants
{
    /** @var list<positive-int> */
    private const WIDTHS = [640, 1280];

    public function generate(string $originalPath): bool
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($originalPath)) {
            return false;
        }

        $contents = $disk->get($originalPath);

        if ($contents === null) {
            return false;
        }

        try {
            $source = Image::fromBytes($contents);
            $sourceWidth = $source->width();
        } catch (ImageException) {
            return false;
        }

        $variantPaths = $this->paths($originalPath);
        $variants = [];

        try {
            foreach ($variantPaths as $width => $variantPath) {
                if ($width > $sourceWidth) {
                    continue;
                }

                $variants[$variantPath] = $source
                    ->scale(width: $width)
                    ->toWebp()
                    ->quality(82)
                    ->toBytes();
            }
        } catch (ImageException) {
            return false;
        }

        foreach ($variants as $variantPath => $variant) {
            if (! $disk->put($variantPath, $variant)) {
                return false;
            }
        }

        $obsoletePaths = array_diff(array_values($variantPaths), array_keys($variants));

        if ($obsoletePaths !== [] && ! $disk->delete($obsoletePaths)) {
            return false;
        }

        return true;
    }

    public function delete(string $originalPath): void
    {
        Storage::disk('public')->delete(array_values($this->paths($originalPath)));
    }

    public function hasRequiredVariants(string $originalPath): bool
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($originalPath)) {
            return false;
        }

        try {
            $sourceWidth = Image::fromStorage($originalPath, 'public')->width();
        } catch (ImageException) {
            return false;
        }

        foreach ($this->paths($originalPath) as $width => $variantPath) {
            if ($width > $sourceWidth) {
                if ($disk->exists($variantPath)) {
                    return false;
                }

                continue;
            }

            if (! $disk->exists($variantPath)) {
                return false;
            }

            try {
                $variant = Image::fromStorage($variantPath, 'public');
                $variantWidth = $variant->width();
                $variantMimeType = $variant->mimeType();
            } catch (ImageException) {
                return false;
            }

            if ($variantWidth !== $width || $variantMimeType !== 'image/webp') {
                return false;
            }
        }

        return true;
    }

    public function srcset(?string $originalPath): ?string
    {
        if (blank($originalPath)) {
            return null;
        }

        $disk = Storage::disk('public');
        $sources = [];

        foreach ($this->paths($originalPath) as $width => $variantPath) {
            if ($disk->exists($variantPath)) {
                $sources[] = "{$disk->url($variantPath)} {$width}w";
            }
        }

        return $sources === [] ? null : implode(', ', $sources);
    }

    /** @return array<positive-int, string> */
    public function paths(string $originalPath): array
    {
        $directory = pathinfo($originalPath, PATHINFO_DIRNAME);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $responsiveDirectory = $directory === '.'
            ? 'responsive'
            : "{$directory}/responsive";

        $paths = [];

        foreach (self::WIDTHS as $width) {
            $paths[$width] = "{$responsiveDirectory}/{$filename}-{$width}.webp";
        }

        return $paths;
    }
}
