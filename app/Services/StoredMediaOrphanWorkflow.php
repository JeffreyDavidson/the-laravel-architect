<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use RalphJSmit\Laravel\SEO\Models\SEO;
use Throwable;

/**
 * Finds files on the public disk that no content references. Trashed content still
 * counts as a reference, so restoring a post or episode never brings back broken media.
 */
final class StoredMediaOrphanWorkflow
{
    private const array OWNED_DIRECTORIES = ['projects/', 'posts/', 'podcasts/', 'episodes/images/', 'episodes/audio/'];

    private const array CONTENT_ATTRIBUTES = [
        [Post::class, 'content'],
        [Project::class, 'content'],
        [Podcast::class, 'long_description'],
        [Episode::class, 'show_notes'],
        [Episode::class, 'transcript'],
        [NewsletterIssue::class, 'content'],
        [SEO::class, 'image'],
    ];

    private const array MEDIA_ATTRIBUTES = [
        [Project::class, 'featured_image_path'],
        [Post::class, 'featured_image_path'],
        [Podcast::class, 'cover_image_path'],
        [Episode::class, 'featured_image_path'],
    ];

    public function __construct(private readonly ResponsiveImageVariants $images) {}

    /**
     * Report unreferenced files and, when $delete is true, remove the eligible ones.
     *
     * Before deleting, every reference is read again once, so a file that content started to
     * use while the storage scan ran is kept. That one fresh read replaces a full re-scan per
     * orphan, which ran the reference queries (including the long text columns) once for every
     * file and grew with the number of orphans. Files uploaded in the last 24 hours are never
     * deleted, so new uploads stay safe; only an old orphan that content starts to use again
     * during the delete loop itself could still be removed, as it could between the per-file
     * re-scan and its delete before.
     *
     * @return array{
     *     orphaned: int,
     *     orphanedBytes: int,
     *     deleted: int,
     *     failed: int,
     *     skipped: int,
     *     missing: int,
     *     files: list<array{path: string, size: int, deleted: bool}>
     * }
     */
    public function audit(bool $delete): array
    {
        $disk = Storage::disk('public');
        $references = $this->references();
        $content = $this->contentReferences();
        $files = [];
        $orphanedBytes = 0;

        foreach ($disk->allFiles() as $file) {
            $path = $this->normalize($file);

            if ($path === '' || str_starts_with(basename($path), '.')) {
                continue;
            }

            if (isset($references['paths'][$path]) || $this->isEmbedded($path, $content)) {
                continue;
            }

            $size = $this->size($disk, $path);
            $orphanedBytes += $size;
            $files[] = [
                'path' => $path,
                'size' => $size,
                'deleted' => false,
            ];
        }

        usort($files, fn (array $left, array $right): int => $right['size'] <=> $left['size']);

        $deleted = 0;
        $failed = 0;
        $skipped = 0;

        if ($delete && $files !== []) {
            $currentReferences = $this->references();
            $currentContent = $this->contentReferences();

            foreach ($files as $index => $file) {
                try {
                    if (! array_any(self::OWNED_DIRECTORIES, fn (string $directory): bool => str_starts_with($file['path'], $directory))
                        || $disk->lastModified($file['path']) > now()->subDay()
                            ->getTimestamp()
                        || isset($currentReferences['paths'][$file['path']])
                        || $this->isEmbedded($file['path'], $currentContent)) {
                        $skipped++;

                        continue;
                    }

                    $wasDeleted = $disk->delete($file['path']);
                } catch (Throwable) {
                    $wasDeleted = false;
                }

                if (! $wasDeleted) {
                    $failed++;

                    continue;
                }

                $deleted++;
                $files[$index]['deleted'] = true;
            }
        }

        $missing = 0;

        foreach (array_keys($references['sources']) as $source) {
            if (! $disk->exists($source)) {
                $missing++;
            }
        }

        return [
            'orphaned' => count($files),
            'orphanedBytes' => $orphanedBytes,
            'deleted' => $deleted,
            'failed' => $failed,
            'skipped' => $skipped,
            'missing' => $missing,
            'files' => $files,
        ];
    }

    /** @return list<string> */
    private function contentReferences(): array
    {
        $content = [];

        foreach (self::CONTENT_ATTRIBUTES as [$modelClass, $column]) {
            foreach ($modelClass::query()
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->whereNotNull($column)
                ->pluck($column) as $value) {
                if (is_string($value)) {
                    $content[] = rawurldecode($value);
                }
            }
        }

        return $content;
    }

    /** @param list<string> $content */
    private function isEmbedded(string $path, array $content): bool
    {
        return array_any($content, fn (string $value): bool => str_contains($value, $path));
    }

    /**
     * @return array{paths: array<string, true>, sources: array<string, true>}
     */
    private function references(): array
    {
        $paths = [];
        $sources = [];

        foreach (self::MEDIA_ATTRIBUTES as [$modelClass, $column]) {
            $modelClass::query()
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->whereNotNull($column)
                ->select([$column])
                ->cursor()
                ->each(function (Model $model) use (&$paths, &$sources, $column): void {
                    $value = $model->getAttribute($column);

                    if (! is_string($value) || blank($value)) {
                        return;
                    }

                    $source = $this->normalize($value);
                    $sources[$source] = true;
                    $paths[$source] = true;

                    foreach ($this->images->paths($source) as $variant) {
                        $paths[$variant] = true;
                    }
                });
        }

        return ['paths' => $paths, 'sources' => $sources];
    }

    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    private function size(FilesystemAdapter $disk, string $path): int
    {
        try {
            return (int) $disk->size($path);
        } catch (Throwable) {
            return 0;
        }
    }
}
