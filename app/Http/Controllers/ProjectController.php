<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::published()
            ->with('tags')
            ->orderBy('sort_order')
            ->get();

        seo()->for(new SEOData(
            title: 'Projects',
            description: 'Open source projects and side projects by Jeffrey Davidson — including Ringside, Campus Sync, and more built with Laravel.',
        ));

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        abort_unless($project->status === 'published', 404);

        $project->load('tags');

        $otherProjects = Project::published()
            ->where('id', '!=', $project->id)
            ->with('tags')
            ->orderBy('sort_order')
            ->get();

        return view('projects.show', compact('project', 'otherProjects'));
    }
}
