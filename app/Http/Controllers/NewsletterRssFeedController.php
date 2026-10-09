<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ViewModels\NewsletterRssFeedViewModel;
use Illuminate\Http\Response;
use JeffreyDavidson\CreatorKit\Support\Feeds\RssChannelRenderer;

final class NewsletterRssFeedController
{
    public function __invoke(NewsletterRssFeedViewModel $viewModel, RssChannelRenderer $renderer): Response
    {
        return response($renderer->render(...$viewModel->data()))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
