<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Feeds\RssChannelRenderer;
use App\ViewModels\RssFeedViewModel;
use Illuminate\Http\Response;

final class RssFeedController
{
    public function __invoke(RssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
