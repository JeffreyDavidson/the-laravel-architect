<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ResponsiveImageLifecycle;

class ProjectObserver
{
    public function __construct(private readonly ResponsiveImageLifecycle $lifecycle) {}

    public function created(Project $project): void
    {
        $this->lifecycle->created($project, 'featured_image_path', 'project');
    }

    public function updated(Project $project): void
    {
        $this->lifecycle->updated($project, 'featured_image_path', 'project');
    }

    public function deleted(Project $project): void
    {
        $this->lifecycle->deleted($project, 'featured_image_path');
    }
}
