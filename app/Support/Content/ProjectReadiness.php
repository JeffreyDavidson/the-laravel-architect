<?php

declare(strict_types=1);

namespace App\Support\Content;

use App\Models\Project;

final readonly class ProjectReadiness
{
    private ContentReadiness $readiness;

    public function __construct(Project $project)
    {
        $this->readiness = new ContentReadiness($project);
    }

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    public function checks(): array
    {
        return $this->readiness->checks();
    }

    public function isReady(): bool
    {
        return $this->readiness->isReady();
    }

    public function label(): string
    {
        return $this->readiness->label();
    }

    public function progress(): string
    {
        return $this->readiness->progress();
    }

    public function missingSummary(): string
    {
        return $this->readiness->missingSummary();
    }
}
