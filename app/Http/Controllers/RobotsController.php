<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\RobotsTxtViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\RobotsTxtRenderer;

final class RobotsController
{
    public function __invoke(RobotsTxtViewModel $viewModel, RobotsTxtRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
