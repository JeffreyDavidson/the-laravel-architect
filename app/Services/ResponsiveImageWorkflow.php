<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use Illuminate\Database\Eloquent\Model;

final readonly class ResponsiveImageWorkflow
{
    public function __construct(private ResponsiveImageVariants $images) {}

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{generated: int, skipped: int, failed: int}
     */
    public function generate(
        string $modelClass,
        string $pathColumn,
        string $label,
        bool $force,
        Closure $warning,
    ): array {
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        $modelClass::query()
            ->whereNotNull($pathColumn)
            ->select(['id', $pathColumn])
            ->eachById(function (Model $model) use ($pathColumn, $label, $force, $warning, &$generated, &$skipped, &$failed): void {
                $sourcePath = $model->getAttribute($pathColumn);

                if (! $force && is_string($sourcePath) && $this->images->hasRequiredVariants($sourcePath)) {
                    $skipped++;

                    return;
                }

                if (is_string($sourcePath) && $this->images->generate($sourcePath)) {
                    $generated++;

                    return;
                }

                $failed++;
                $key = $model->getKey();

                if (! is_int($key) && ! is_string($key)) {
                    $key = 'unknown';
                }

                $warning("Skipped {$label} {$key}: its source image is missing or unsupported.");
            });

        return ['generated' => $generated, 'skipped' => $skipped, 'failed' => $failed];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array{checked: int, failed: int}
     */
    public function verify(string $modelClass, string $pathColumn): array
    {
        $checked = 0;
        $failed = 0;

        $modelClass::query()
            ->whereNotNull($pathColumn)
            ->select(['id', $pathColumn])
            ->eachById(function (Model $model) use ($pathColumn, &$checked, &$failed): void {
                $checked++;
                $sourcePath = $model->getAttribute($pathColumn);

                if (! is_string($sourcePath) || ! $this->images->hasRequiredVariants($sourcePath)) {
                    $failed++;
                }
            });

        return ['checked' => $checked, 'failed' => $failed];
    }
}
