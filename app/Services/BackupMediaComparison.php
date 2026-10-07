<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\BackupComparisonResult;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Maps archived media entries to their live paths and compares the archived
 * media with the live media directories: the file count must match and a
 * random sample of SHA-256 hashes must match the live copies.
 */
final class BackupMediaComparison
{
    private const int SAMPLED_MEDIA_FILES = 5;

    /**
     * The live path an archive entry was backed up from, or null when the
     * entry is outside every backed-up media directory. Archives store media
     * under its absolute path without the leading slash.
     */
    public function livePathFor(string $name): ?string
    {
        foreach ($this->mediaRoots() as $root) {
            $prefix = ltrim($root, '/').'/';

            if (str_starts_with($name, $prefix)) {
                return "{$root}/".substr($name, strlen($prefix));
            }
        }

        return null;
    }

    /** @param array<string, string> $mediaHashes Archived media SHA-256 hashes keyed by their live path. */
    public function compare(array $mediaHashes): BackupComparisonResult
    {
        $archivedCount = count($mediaHashes);
        $liveCount = count($this->liveMediaFiles());

        if ($archivedCount !== $liveCount) {
            return new BackupComparisonResult(failures: ["The archive has {$archivedCount} media files but the live media directory has {$liveCount}."]);
        }

        $sample = $archivedCount === 0
            ? []
            : (array) array_rand($mediaHashes, min(self::SAMPLED_MEDIA_FILES, $archivedCount));

        foreach ($sample as $livePath) {
            if (! is_string($livePath) || ! is_file($livePath) || hash_file('sha256', $livePath) !== $mediaHashes[$livePath]) {
                return new BackupComparisonResult(failures: ['A sampled media file does not match its live copy.']);
            }
        }

        $sampleCount = count($sample);

        return new BackupComparisonResult(checks: ["All {$archivedCount} media files are present; {$sampleCount} sampled SHA-256 hashes match."]);
    }

    /** @return list<string> */
    private function mediaRoots(): array
    {
        $roots = [];

        foreach (config()->array('backup.backup.source.files.include') as $root) {
            if (is_string($root) && $root !== '') {
                $roots[] = rtrim($root, '/');
            }
        }

        return $roots;
    }

    /** @return list<string> */
    private function liveMediaFiles(): array
    {
        $files = [];

        foreach ($this->mediaRoots() as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo) {
                    continue;
                }

                $isRegularFile = $file->isFile();
                $isLink = $file->isLink();

                if ($isRegularFile && ! $isLink) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
