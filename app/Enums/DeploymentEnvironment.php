<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The deployment a running copy of the site belongs to, read from
 * TLA_DEPLOYMENT_ENVIRONMENT (app.deployment_environment). Staging runs with
 * APP_ENV=production, so this, not APP_ENV, tells production and staging apart.
 */
enum DeploymentEnvironment: string
{
    case Production = 'production';
    case Staging = 'staging';

    /**
     * The configured deployment, or null when the value is missing or is not a
     * deployment (such as local or testing).
     */
    public static function current(): ?self
    {
        $environment = config('app.deployment_environment');

        return is_string($environment)
            ? self::tryFrom($environment)
            : null;
    }
}
