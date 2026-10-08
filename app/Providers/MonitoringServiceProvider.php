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

    public function boot(): void
    {
        Nightwatch::user(app(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents(app(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands(app(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions(app(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests(app(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries(app(RedactNightwatchQuery::class));
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));
    }
}
