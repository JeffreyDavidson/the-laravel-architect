<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Monitoring\Nightwatch\RedactNightwatchCacheEvent;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCommand;
use App\Support\Monitoring\Nightwatch\RedactNightwatchException;
use App\Support\Monitoring\Nightwatch\RedactNightwatchOutgoingRequest;
use App\Support\Monitoring\Nightwatch\RedactNightwatchQuery;
use App\Support\Monitoring\Nightwatch\RedactNightwatchRequest;
use App\Support\Monitoring\Nightwatch\ResolveNightwatchUser;
use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Facades\Nightwatch;
use Sentry\ClientBuilder;

/**
 * Wires the error and performance telemetry: Sentry's redaction callbacks and
 * Nightwatch's user resolver and redaction callbacks. Sampling rates live in
 * `config/sentry.php` and `config/nightwatch.php`. The `/up` health checks are
 * the discovered `App\Listeners\CheckApplicationHealthListener`.
 */
final class MonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $application = $this->app;

        $application->afterResolving(ClientBuilder::class, function (ClientBuilder $clientBuilder) use ($application): void {
            $options = $clientBuilder->getOptions();
            $beforeSend = $application->make(RedactSentryEvent::class);
            $beforeBreadcrumb = $application->make(RedactSentryBreadcrumb::class);
            $options->setBeforeSendCallback($beforeSend);
            $options->setBeforeBreadcrumbCallback($beforeBreadcrumb);
        });
    }

    /**
     * Nightwatch's own provider binds its Core while registering. When that provider isn't
     * loaded there is nothing to redact, and resolving the facade would fail the whole boot.
     */
    public function boot(): void
    {
        if (! $this->app->bound(Core::class)) {
            return;
        }

        Nightwatch::user($this->app->make(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents($this->app->make(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands($this->app->make(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions($this->app->make(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests($this->app->make(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries($this->app->make(RedactNightwatchQuery::class));
        Nightwatch::redactRequests($this->app->make(RedactNightwatchRequest::class));
    }
}
