<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Project;
use App\Services\StoredMediaLifecycle;

final readonly class ProjectObserver
{
    public function __construct(private StoredMediaLifecycle $media) {}

    public function created(Project $project): void
    {
        $this->media->created($project, 'featured_image_path', 'project');
    }

    public function updated(Project $project): void
    {
        $this->media->updated($project, 'featured_image_path', 'project');
    }

    public function forceDeleted(Project $project): void
    {
        $this->media->forceDeleted($project, 'featured_image_path', 'project');
    }
}
