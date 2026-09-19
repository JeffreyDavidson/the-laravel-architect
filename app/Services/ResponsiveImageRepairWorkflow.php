<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

final readonly class ResponsiveImageRepairWorkflow
{
    public function __construct(
        private readonly ResponsiveImageVariants $images,
        private readonly ResponsiveImageWorkflow $workflow,
    ) {}

    /**
     * @return array{
     *     generations: array<string, array{generated: int, skipped: int, failed: int}>,
     *     verification: array<string, array{checked: int, failed: int}>,
     *     warnings: list<string>,
     * }
     */
    public function repair(bool $force): array
    {
        $warnings = [];
        $generations = [];

        foreach ($this->resources() as $resource) {
            $generations[$resource['label']] = $this->workflow->generate(
                $resource['class'],
                $resource['column'],
                $resource['label'],
                $force,
                $this->images,
                function (string $warning) use (&$warnings): void {
                    $warnings[] = $warning;
                },
            );
        }

        $verification = [];

        foreach ($this->resources() as $resource) {
            $verification[$resource['label']] = $this->workflow->verify(
                $resource['class'],
                $resource['column'],
                $this->images,
            );
        }

        return [
            'generations' => $generations,
            'verification' => $verification,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return list<array{class: class-string<Model>, column: string, label: string}>
     */
    private function resources(): array
    {
        return [
            ['class' => Project::class, 'column' => 'featured_image_path', 'label' => 'project'],
            ['class' => Post::class, 'column' => 'featured_image_path', 'label' => 'post'],
            ['class' => Podcast::class, 'column' => 'cover_image_path', 'label' => 'podcast'],
        ];
    }
}
