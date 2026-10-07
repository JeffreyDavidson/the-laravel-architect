<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\ViewModels\ProjectShowViewModel;
use Illuminate\Contracts\View\View;

final class PreviewProjectController
{
    public function __invoke(Project $project, ProjectShowViewModel $viewModel): View
    {
        return view('pages.projects.show', $viewModel->previewData($project));
    }
}
