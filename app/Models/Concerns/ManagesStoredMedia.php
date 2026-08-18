<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

trait ManagesStoredMedia
{
    public static function bootManagesStoredMedia(): void
    {
        static::updated(function (Model $model): void {
            foreach ($model->storedMediaAttributes() as $attribute) {
                if (! $model->wasChanged($attribute)) {
                    continue;
                }

                $model->deleteStoredMediaPath($model->getPrevious()[$attribute] ?? null);
            }
        });

        static::deleted(function (Model $model): void {
            $model->deleteStoredMediaFiles();
        });
    }

    public function deleteStoredMediaFiles(): void
    {
        foreach ($this->storedMediaAttributes() as $attribute) {
            $this->deleteStoredMediaPath($this->getAttribute($attribute));
        }
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
}
