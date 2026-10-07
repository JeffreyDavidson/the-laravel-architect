<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ProjectReadinessFilter;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

final readonly class ProjectReadinessQuery
{
    public function __construct(private ContentReadinessQuery $readiness) {}

    /** @param Builder<Project> $query */
    public function apply(Builder $query, ?string $filter): void
    {
        $readinessFilter = ProjectReadinessFilter::tryFrom((string) $filter);

        if ($readinessFilter === null) {
            return;
        }

        if ($readinessFilter === ProjectReadinessFilter::Ready) {
            $this->readiness->whereReady($query);

            return;
        }

        $this->readiness->whereIncomplete($query, match ($readinessFilter) {
            ProjectReadinessFilter::NeedsImage => ['featured_image'],
            ProjectReadinessFilter::NeedsCaseStudy => ['case_study'],
            ProjectReadinessFilter::NeedsDetails => ['description', 'project_link', 'tech_stack', 'tags'],
        });
    }
}
