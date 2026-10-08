<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProjectIndexRequest;
use App\Models\Project;
use App\ViewModels\ProjectIndexViewModel;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Contracts\View\View;

final class ProjectController
{
    public function index(ProjectIndexRequest $request, ProjectIndexViewModel $projectIndexViewModel): View
    {
        return view('pages.projects.index', $projectIndexViewModel->data(
            $request->technology(),
            $request->tag(),
        ));
    }

    public function show(Project $project, ProjectShowViewModel $projectShowViewModel): View
    {
        abort_unless($project->isPublished(), 404);

        return view('pages.projects.show', $projectShowViewModel->data($project));
    }
}
