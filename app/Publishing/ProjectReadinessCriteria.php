<?php

declare(strict_types=1);

namespace App\Publishing;

use App\Enums\ProjectReadinessFilter;
use App\Enums\ReadinessCheck;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies the projects table's readiness filter to the builder it is given.
 */
final readonly class ProjectReadinessCriteria
{
    public function __construct(private ContentReadinessCriteria $readiness) {}

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
            ProjectReadinessFilter::NeedsImage => [ReadinessCheck::FeaturedImage],
            ProjectReadinessFilter::NeedsCaseStudy => [ReadinessCheck::CaseStudy],
            ProjectReadinessFilter::NeedsDetails => [ReadinessCheck::Description, ReadinessCheck::ProjectLink, ReadinessCheck::TechStack, ReadinessCheck::Tags],
        });
    }
}
