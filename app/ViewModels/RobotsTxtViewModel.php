<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\DeploymentEnvironment;

final class RobotsTxtViewModel
{
    /**
     * The robots.txt policy for the current deployment. Only production is crawlable;
     * staging runs with APP_ENV=production, so the deployment environment decides.
     * Search results stay crawlable on purpose: they are marked noindex, and a crawler
     * must be able to fetch a page to read that tag.
     *
     * @return array{allowsCrawling: bool, disallowedPaths: list<string>, sitemapUrl: string}
     */
    public function data(): array
    {
        return [
            'allowsCrawling' => DeploymentEnvironment::current() === DeploymentEnvironment::Production,
            'disallowedPaths' => ['/admin', '/admin/*', '/preview/'],
            'sitemapUrl' => route('sitemap'),
        ];
    }
}
