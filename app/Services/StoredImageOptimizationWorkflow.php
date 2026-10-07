<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ImageOptimizationOutcome;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class StoredImageOptimizationWorkflow
{
    public function __construct(private ImageUploadOptimizer $optimizer) {}

    /**
     * @return array{results: list<array{label: string, optimized: int, skipped: int, failed: int}>, failed: int}
     */
    public function optimizeAll(bool $dryRun, bool $force, Closure $warning): array
    {
        $results = [];

        foreach ($this->resources() as [$modelClass, $pathColumn, $directory, $label]) {
            $results[] = [
                'label' => $label,
                ...$this->optimize($modelClass, $pathColumn, $directory, $label, $dryRun, $force, $warning),
            ];
        }

        return [
            'results' => $results,
            'failed' => array_sum(array_column($results, 'failed')),
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{optimized: int, skipped: int, failed: int}
     */
    public function optimize(
        string $modelClass,
        string $pathColumn,
        string $directory,
        string $label,
        bool $dryRun,
        bool $force,
        Closure $warning,
    ): array {
        $counts = ['optimized' => 0, 'skipped' => 0, 'failed' => 0];

        $records = $modelClass::query()
            ->whereNotNull($pathColumn)
            ->select(['id', $pathColumn])
            ->lazyById();

        foreach ($records as $record) {
            $outcome = $this->optimizeRecord($record, $pathColumn, $directory, $dryRun, $force);

            if ($outcome === ImageOptimizationOutcome::Failed) {
                $this->warnFailure($record, $label, $warning);
            }

            $counts[$outcome->value]++;
        }

        return $counts;
    }

    /**
     * Replace one record's stored image with an optimized WebP copy. A new
     * file is deleted again when it is invalid or its path could not be saved.
     */
    private function optimizeRecord(
        Model $record,
        string $pathColumn,
        string $directory,
        bool $dryRun,
        bool $force,
    ): ImageOptimizationOutcome {
        $disk = Storage::disk('public');
        $sourcePath = $record->getAttribute($pathColumn);

        if (! is_string($sourcePath) || blank($sourcePath)) {
            return ImageOptimizationOutcome::Failed;
        }

        if (! $force && str_ends_with(strtolower($sourcePath), '.webp') && $disk->exists($sourcePath)) {
            return ImageOptimizationOutcome::Skipped;
        }

        try {
            $contents = $disk->get($sourcePath);
        } catch (Throwable) {
            return ImageOptimizationOutcome::Failed;
        }

        if (! is_string($contents)) {
            return ImageOptimizationOutcome::Failed;
        }

        $optimizedContents = $this->optimizer->optimize($contents);

        if ($optimizedContents === null) {
            return ImageOptimizationOutcome::Failed;
        }

        if ($dryRun) {
            return ImageOptimizationOutcome::Optimized;
        }

        try {
            $newPath = $this->optimizer->storeOptimizedContents($optimizedContents, $directory, 'public');
        } catch (Throwable) {
            return ImageOptimizationOutcome::Failed;
        }

        if (! is_string($newPath) || ! $this->isValidOptimizedImage($newPath)) {
            if (is_string($newPath)) {
                $disk->delete($newPath);
            }

            return ImageOptimizationOutcome::Failed;
        }

        try {
            $record->getConnection()
                ->transaction(function () use ($record, $pathColumn, $newPath): void {
                    $record->setAttribute($pathColumn, $newPath);
                    $record->saveOrFail();
                });
        } catch (Throwable) {
            // After-commit callbacks can fail after the new path is already durable.
            if ($record->newQuery()
                ->whereKey($record->getKey())
                ->value($pathColumn) !== $newPath) {
                $disk->delete($newPath);
            }

            return ImageOptimizationOutcome::Failed;
        }

        return ImageOptimizationOutcome::Optimized;
    }

    private function isValidOptimizedImage(string $path): bool
    {
        try {
            $image = Image::fromStorage($path, 'public');
        } catch (Throwable) {
            return false;
        }

        return $image->mimeType() === 'image/webp'
            && $image->width() <= ImageUploadOptimizer::MAX_DIMENSION
            && $image->height() <= ImageUploadOptimizer::MAX_DIMENSION;
    }

    private function warnFailure(Model $record, string $label, Closure $warning): void
    {
        $key = $record->getKey();

        if (! is_int($key) && ! is_string($key)) {
            $key = 'unknown';
        }

        $warning("Skipped {$label} {$key}: its source image could not be optimized.");
    }

    /**
     * @return list<array{class-string<Model>, string, string, string}>
     */
    private function resources(): array
    {
        return [
            [Project::class, 'featured_image_path', 'projects', 'project'],
            [Post::class, 'featured_image_path', 'posts', 'post'],
            [Podcast::class, 'cover_image_path', 'podcasts', 'podcast'],
            [Episode::class, 'featured_image_path', 'episodes/images', 'episode'],
        ];
    }
}
