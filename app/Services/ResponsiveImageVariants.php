<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Exceptions\DecoderException;
use Intervention\Image\ImageManager;

class ResponsiveImageVariants
{
    /** @var list<int> */
    private const WIDTHS = [640, 1280];

    public function generate(string $originalPath): bool
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($originalPath)) {
            return false;
        }

        $contents = $disk->get($originalPath);
        $manager = new ImageManager(new Driver);

        try {
            $source = $manager->read($contents);
        } catch (DecoderException) {
            return false;
        }

        $this->delete($originalPath);

        foreach ($this->paths($originalPath) as $width => $variantPath) {
            if ($width > $source->width()) {
                continue;
            }

            $variant = $manager
                ->read($contents)
                ->scaleDown(width: $width)
                ->encode(new WebpEncoder(quality: 82, strip: true));

            $disk->put($variantPath, (string) $variant);
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

        $manager = new ImageManager(new Driver);

        try {
            $source = $manager->read($disk->get($originalPath));
        } catch (DecoderException) {
            return false;
        }

        foreach ($this->paths($originalPath) as $width => $variantPath) {
            if ($width > $source->width()) {
                continue;
            }

            if (! $disk->exists($variantPath)) {
                return false;
            }

            try {
                $variant = $manager->read($disk->get($variantPath));
            } catch (DecoderException) {
                return false;
            }

            if ($variant->width() !== $width || $variant->origin()->mediaType() !== 'image/webp') {
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

    /** @return array<int, string> */
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
