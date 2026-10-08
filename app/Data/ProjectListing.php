<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Tags\Tag;

/**
 * The published projects narrowed to the requested filters, with the filter options every
 * published project offers. A requested filter that no published project offers leaves its
 * matched value null.
 */
final readonly class ProjectListing
{
    /**
     * @param  Collection<int, Project>  $projects
     * @param  list<non-empty-string>  $technologies
     * @param  list<Tag>  $tags
     */
    public function __construct(
        public Collection $projects,
        public array $technologies,
        public array $tags,
        public ?string $technology,
        public ?Tag $tag,
    ) {}
}
