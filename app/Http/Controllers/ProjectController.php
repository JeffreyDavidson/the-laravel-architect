<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::published()
            ->with('tags')
            ->orderBy('sort_order')
            ->get();

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
