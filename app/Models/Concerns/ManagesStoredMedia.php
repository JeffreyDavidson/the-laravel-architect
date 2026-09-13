<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait ManagesStoredMedia
{
    public static function bootManagesStoredMedia(): void
    {
        static::updated(function (self $model): void {
            foreach ($model->storedMediaAttributes() as $attribute) {
                if (! $model->wasChanged($attribute)) {
                    continue;
                }

                $model->queueStoredMediaPathDeletion($model->getPrevious()[$attribute] ?? null);
            }
        });

        static::deleted(function (self $model): void {
            $model->queueStoredMediaCleanup();
        });
    }

    public function deleteStoredMediaFiles(): void
    {
        foreach ($this->storedMediaAttributes() as $attribute) {
            $this->deleteStoredMediaPath($this->getAttribute($attribute));
        }
    }

    public function queueStoredMediaCleanup(): void
    {
        $this->queueStoredMediaPathsCleanup($this->storedMediaPaths());
    }

    /** @param array<int, string> $paths */
    public function queueStoredMediaPathsCleanup(array $paths): void
    {
        foreach ($paths as $path) {
            $this->queueStoredMediaPathDeletion($path);
        }
    }

    /** @return list<string> */
    public function storedMediaPaths(): array
    {
        $paths = [];

        foreach ($this->storedMediaAttributes() as $attribute) {
            $path = $this->getAttribute($attribute);

            if (is_string($path) && filled($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /** @return array<int, string> */
    abstract protected function storedMediaAttributes(): array;

    private function deleteStoredMediaPath(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function queueStoredMediaPathDeletion(mixed $path): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        $path = (string) $path;

        $this->getConnection()->afterCommit(function () use ($path): void {
            Storage::disk('public')->delete($path);
        });
    }
}
