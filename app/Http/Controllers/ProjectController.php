<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::published()
            ->with('tags')
            ->orderBy('sort_order')
            ->get();

        $seoSource = new SEOData(
            title: 'Projects',
            description: 'Open source projects and side projects by Jeffrey Davidson — including Ringside, Campus Sync, and more built with Laravel.',
        );

        return view('projects.index', compact('projects', 'seoSource'));
    }

    public function show(Project $project): View
    {
        abort_unless($project->status === ProjectStatus::Published, 404);

        $project->load('tags');

        $otherProjects = Project::published()
            ->where('id', '!=', $project->id)
            ->with('tags')
            ->orderBy('sort_order')
            ->get();
        $seoSource = $project;

        return view('projects.show', compact('project', 'otherProjects', 'seoSource'));
    }
}
