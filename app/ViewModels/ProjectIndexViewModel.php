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
                ->portfolio()
                ->with('tags')
                ->orderBy('sort_order')
                ->get(),
            'seoSource' => new SEOData(
                title: 'Projects',
                description: 'Explore projects by Jeffrey Davidson: the products, the problems they solve, and the work behind them, built with Laravel.',
            ),
        ];
    }
}
