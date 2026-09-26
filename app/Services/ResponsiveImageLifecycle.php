<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ResponsiveImageLifecycle
{
    public function __construct(private readonly ResponsiveImageVariants $images) {}

    public function created(Model $model, string $pathColumn, string $label): void
    {
        $this->generate($model->getAttribute($pathColumn), $label);
    }

    public function updated(Model $model, string $pathColumn, string $label): void
    {
        if (! $model->wasChanged($pathColumn)) {
            return;
        }

        $previousPath = $model->getPrevious()[$pathColumn] ?? null;

        if (is_string($previousPath) && filled($previousPath)) {
            $this->deleteAfterCommit($model, $previousPath);
        }

        $this->generate($model->getAttribute($pathColumn), $label);
    }

    public function deleted(Model $model, string $pathColumn): void
    {
        $path = $model->getAttribute($pathColumn);

        if (is_string($path) && filled($path)) {
            $this->deleteAfterCommit($model, $path);
        }
    }

    private function generate(mixed $path, string $label): void
    {
        if (! is_string($path) || blank($path)) {
            return;
        }

        if (! $this->images->generate($path)) {
            Log::warning("Responsive {$label} image generation failed. Run {$label}s:generate-image-variants to retry.");
        }
    }

    private function deleteAfterCommit(Model $model, string $path): void
    {
        $model->getConnection()
            ->afterCommit(function () use ($path): void {
                $this->images->delete($path);
            });
    }
}
