<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Keeps a model's stored media files in step with its database row: the original upload
 * on the public disk and, for attributes that have them, its responsive WebP variants.
 *
 * Observers call it for each media attribute. Every file change waits for the database
 * commit, so a rolled back save or delete leaves the files alone. Variant generation runs
 * synchronously once the commit lands. A soft delete or restore never touches files.
 */
final readonly class StoredMediaLifecycle
{
    public function __construct(private ResponsiveImageVariants $variants) {}

    /**
     * Generate the responsive variants for a new record's media after it commits.
     *
     * @param  ?string  $variantLabel  The content label used in the failure warning ("post"),
     *                                 or null when the attribute has no responsive variants.
     */
    public function created(Model $model, string $attribute, ?string $variantLabel = null): void
    {
        $path = $this->storedPath($model->getAttribute($attribute));

        if ($path === null || $variantLabel === null) {
            return;
        }

        $this->afterCommit($model, function () use ($path, $variantLabel): void {
            $this->generateVariants($path, $variantLabel);
        });
    }

    /**
     * After a replaced media path commits, delete the previous original with its variants
     * and generate the variants for the new file.
     *
     * @param  ?string  $variantLabel  See created().
     */
    public function updated(Model $model, string $attribute, ?string $variantLabel = null): void
    {
        if (! $model->wasChanged($attribute)) {
            return;
        }

        $previousPath = $this->storedPath($model->getPrevious()[$attribute] ?? null);
        $path = $this->storedPath($model->getAttribute($attribute));

        $this->afterCommit($model, function () use ($previousPath, $path, $variantLabel): void {
            if ($previousPath !== null) {
                $this->delete($previousPath, $variantLabel !== null);
            }

            if ($path !== null && $variantLabel !== null) {
                $this->generateVariants($path, $variantLabel);
            }
        });
    }

    /**
     * Delete the original and its variants once the force delete commits.
     *
     * @param  ?string  $variantLabel  See created().
     */
    public function forceDeleted(Model $model, string $attribute, ?string $variantLabel = null): void
    {
        $path = $this->storedPath($model->getAttribute($attribute));

        if ($path === null) {
            return;
        }

        $this->afterCommit($model, function () use ($path, $variantLabel): void {
            $this->delete($path, $variantLabel !== null);
        });
    }

    private function storedPath(mixed $path): ?string
    {
        if (! is_string($path) || blank($path)) {
            return null;
        }

        return $path;
    }

    private function delete(string $path, bool $hasVariants): void
    {
        Storage::disk('public')->delete($path);

        if ($hasVariants) {
            $this->variants->delete($path);
        }
    }

    private function generateVariants(string $path, string $variantLabel): void
    {
        if ($this->variants->generate($path)) {
            return;
        }

        Log::warning("Responsive {$variantLabel} image generation failed. Run media:repair-responsive-images to retry.");
    }

    private function afterCommit(Model $model, Closure $callback): void
    {
        $model->getConnection()
            ->afterCommit($callback);
    }
}
