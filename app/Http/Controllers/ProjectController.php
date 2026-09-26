<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\ViewModels\ProjectIndexViewModel;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectController
{
    public function index(Request $request, ProjectIndexViewModel $projectIndexViewModel): View
    {
        return view('pages.projects.index', $projectIndexViewModel->data([
            'technology' => $request->string('technology')
                ->trim()
                ->toString(),
            'tag' => $request->string('tag')
                ->trim()
                ->toString(),
        ]));
    }

    public function show(Project $project, ProjectShowViewModel $projectShowViewModel): View
    {
        abort_unless($project->isPublished(), 404);

        return view('pages.projects.show', $projectShowViewModel->data($project));
    }
}
