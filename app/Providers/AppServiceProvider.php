<?php

namespace App\Providers;

use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCacheEvent;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCommand;
use App\Support\Monitoring\Nightwatch\RedactNightwatchException;
use App\Support\Monitoring\Nightwatch\RedactNightwatchOutgoingRequest;
use App\Support\Monitoring\Nightwatch\RedactNightwatchQuery;
use App\Support\Monitoring\Nightwatch\RedactNightwatchRequest;
use App\Support\Monitoring\Nightwatch\ResolveNightwatchUser;
use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use App\Support\Seo\StructuredDataBuilder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use Laravel\Nightwatch\Facades\Nightwatch;
use Sentry\ClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->afterResolving(ClientBuilder::class, function (ClientBuilder $clientBuilder): void {
            $clientBuilder->getOptions()
                ->setBeforeSendCallback($this->app->make(RedactSentryEvent::class))
                ->setBeforeBreadcrumbCallback($this->app->make(RedactSentryBreadcrumb::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Nightwatch::user(app(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents(app(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands(app(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions(app(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests(app(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries(app(RedactNightwatchQuery::class));
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));

        Event::listen(DiagnosingHealth::class, function (): void {
            DB::table('migrations')->limit(1)->exists();

            if (config('health.runtime.enabled') === true) {
                app(RuntimeHealthMonitor::class)->ensureHealthy();
            }
        });

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('newsletter-confirm', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        $appUrl = config('app.url');

        if (is_string($appUrl) && str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        View::composer([
            'errors.404',
            'pages.*',
        ], function (ViewInstance $view): void {
            /** @var array<string, mixed> $pageData */
            $pageData = $view->getData();

            $view->with(
                'structuredData',
                app(StructuredDataBuilder::class)->build($pageData),
            );
        });
    }
}
