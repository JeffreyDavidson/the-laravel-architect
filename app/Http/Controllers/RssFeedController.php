<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\RssFeedViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\RssChannelRenderer;

final class RssFeedController
{
    public function __invoke(RssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
