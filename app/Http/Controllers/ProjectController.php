<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\ViewModels\ProjectIndexViewModel;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Contracts\View\View;

class ProjectController
{
    public function index(ProjectIndexViewModel $projectIndexViewModel): View
    {
        return view('projects.index', $projectIndexViewModel->data());
    }

    public function show(Project $project, ProjectShowViewModel $projectShowViewModel): View
    {
        abort_unless($project->isPublished(), 404);

        return view('projects.show', $projectShowViewModel->data($project));
    }
}
