<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DeploymentEnvironment;

final class GenerateRobotsTxt
{
    /**
     * Build robots.txt for the current deployment. Only production is crawlable;
     * staging runs with APP_ENV=production, so the deployment environment decides.
     * Search results stay crawlable on purpose: they are marked noindex, and a crawler
     * must be able to fetch a page to read that tag.
     */
    public function handle(): string
    {
        if (DeploymentEnvironment::current() !== DeploymentEnvironment::Production) {
            return "User-agent: *\nDisallow: /\n";
        }

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /admin/*',
            'Disallow: /preview/',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);
    }
}
