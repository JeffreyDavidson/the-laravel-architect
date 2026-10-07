<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Feeds\RssChannelRenderer;
use App\ViewModels\NewsletterRssFeedViewModel;
use Illuminate\Http\Response;

final class NewsletterRssFeedController
{
    public function __invoke(NewsletterRssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
