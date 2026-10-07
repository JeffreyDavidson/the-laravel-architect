<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateRobotsTxt;
use Illuminate\Http\Response;

final class RobotsController
{
    public function __invoke(GenerateRobotsTxt $generateRobotsTxt): Response
    {
        return response($generateRobotsTxt->handle(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
