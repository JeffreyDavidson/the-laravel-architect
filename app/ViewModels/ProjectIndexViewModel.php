<?php

namespace App\ViewModels;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ProjectIndexViewModel
{
    /**
     * @return array{
     *     projects: Collection<int, Project>,
     *     seoSource: SEOData,
     * }
     */
    public function data(): array
    {
        return [
            'projects' => Project::published()
                ->with('tags')
                ->orderBy('sort_order')
                ->get(),
            'seoSource' => new SEOData(
                title: 'Projects',
                description: 'Open source projects and side projects by Jeffrey Davidson — including Ringside, Campus Sync, and more built with Laravel.',
            ),
        ];
    }
}
