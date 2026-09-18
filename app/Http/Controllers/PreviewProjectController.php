<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Contracts\View\View;

class PreviewProjectController
{
    public function __invoke(Project $project, ProjectShowViewModel $viewModel): View
    {
        return view('pages.projects.show', $viewModel->previewData($project));
    }
}
