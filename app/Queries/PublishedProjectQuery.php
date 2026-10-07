<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;

final readonly class PublishedProjectQuery
{
    /** Find the published project a visitor is asking about, or null for a blank, unknown or unpublished slug. */
    public function findBySlug(string $slug): ?Project
    {
        if (blank($slug)) {
            return null;
        }

        return Project::query()
            ->published()
            ->where('slug', $slug)
            ->first();
    }
}
