<?php

declare(strict_types=1);

namespace App\Actions;

final class GenerateRobotsTxt
{
    /**
     * Build robots.txt for the current deployment. Only production is crawlable;
     * staging runs with APP_ENV=production, so the deployment environment decides.
     */
    public function handle(): string
    {
        if (config('app.deployment_environment') !== 'production') {
            return "User-agent: *\nDisallow: /\n";
        }

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /admin',
            'Disallow: /admin/*',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);
    }
}
