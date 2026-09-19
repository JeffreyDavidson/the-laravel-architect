<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoredImageOptimizationWorkflow
{
    public function __construct(private readonly ImageUploadOptimizer $optimizer) {}

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
        $optimized = 0;
        $skipped = 0;
        $failed = 0;
        $disk = Storage::disk('public');

        $modelClass::query()
            ->whereNotNull($pathColumn)
            ->select(['id', $pathColumn])
            ->eachById(function (Model $model) use ($pathColumn, $directory, $label, $dryRun, $force, $disk, $warning, &$optimized, &$skipped, &$failed): void {
                $sourcePath = $model->getAttribute($pathColumn);

                if (! is_string($sourcePath) || blank($sourcePath)) {
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                if (! $force && str_ends_with(strtolower($sourcePath), '.webp') && $disk->exists($sourcePath)) {
                    $skipped++;

                    return;
                }

                try {
                    $contents = $disk->get($sourcePath);
                } catch (Throwable) {
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                if (! is_string($contents)) {
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                $optimizedContents = $this->optimizer->optimize($contents);

                if ($optimizedContents === null) {
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                if ($dryRun) {
                    $optimized++;

                    return;
                }

                try {
                    $newPath = $this->optimizer->storeOptimizedContents($optimizedContents, $directory, 'public');
                } catch (Throwable) {
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                if (! is_string($newPath) || ! $this->isValidOptimizedImage($newPath)) {
                    if (is_string($newPath)) {
                        $disk->delete($newPath);
                    }

                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                try {
                    $model->getConnection()->transaction(function () use ($model, $pathColumn, $newPath): void {
                        $model->setAttribute($pathColumn, $newPath);
                        $model->saveOrFail();
                    });
                } catch (Throwable) {
                    // After-commit callbacks can fail after the new path is already durable.
                    if ($model->newQuery()->whereKey($model->getKey())->value($pathColumn) !== $newPath) {
                        $disk->delete($newPath);
                    }
                    $this->fail($model, $label, $warning, $failed);

                    return;
                }

                $optimized++;
            });

        return ['optimized' => $optimized, 'skipped' => $skipped, 'failed' => $failed];
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

    private function fail(Model $model, string $label, Closure $warning, int &$failed): void
    {
        $failed++;
        $key = $model->getKey();

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
