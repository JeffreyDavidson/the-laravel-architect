<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Feeds\RobotsTxtRenderer;
use App\ViewModels\RobotsTxtViewModel;
use Illuminate\Http\Response;

final class RobotsController
{
    public function __invoke(RobotsTxtViewModel $viewModel, RobotsTxtRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
