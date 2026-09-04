<?php

namespace App\Content;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class ProductionContentSource
{
    public function exportTo(string $archivePath): void
    {
        [$phpBinary, $sitePath] = $this->validatedConfiguration();
        $result = Process::timeout(60)->run([
            $phpBinary,
            "{$sitePath}/artisan",
            'content:export-public',
            $archivePath,
            '--no-interaction',
        ]);

        if ($result->failed()) {
            throw new RuntimeException('Production public-content export failed.');
        }
    }

    /** @param list<string> $paths */
    public function copyMedia(array $paths): void
    {
        [, $sitePath] = $this->validatedConfiguration();
        $sourceRoot = "{$sitePath}/storage/app/public";

        foreach ($paths as $path) {
            $source = "{$sourceRoot}/{$path}";
            $destination = Storage::disk('public')->path($path);

            if (! File::isFile($source)) {
                throw new RuntimeException("Referenced production media is missing: {$path}");
            }

            File::ensureDirectoryExists(dirname($destination));

            if (! File::copy($source, $destination)) {
                throw new RuntimeException("Unable to copy production media: {$path}");
            }
        }
    }

    /** @return array{string, string} */
    private function validatedConfiguration(): array
    {
        $phpBinary = config('content-sync.production.php_binary');
        $sitePath = config('content-sync.production.site_path');

        if (! is_string($phpBinary) || ! is_string($sitePath)) {
            throw new InvalidArgumentException('Production content source configuration is invalid.');
        }

        $sitePath = rtrim($sitePath, '/');

        foreach ([$phpBinary, $sitePath] as $path) {
            if (! str_starts_with($path, '/')
                || preg_match('/\A\/[a-zA-Z0-9._\/-]+\z/', $path) !== 1
                || in_array('..', explode('/', $path), true)) {
                throw new InvalidArgumentException('Production content source configuration is invalid.');
            }
        }

        return [$phpBinary, $sitePath];
    }
}
