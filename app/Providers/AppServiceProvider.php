<?php

namespace App\Providers;

use App\Models\Tag;
use App\Services\PublicPageBenchmark;
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
use App\View\Components\SocialLinks;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use Laravel\Nightwatch\Facades\Nightwatch;
use Livewire\Livewire;
use Sentry\ClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $application = $this->app;
        $application->singleton(PublicPageBenchmark::class);

        $application->afterResolving(ClientBuilder::class, function (ClientBuilder $clientBuilder) use ($application): void {
            $options = $clientBuilder->getOptions();
            $beforeSend = $application->make(RedactSentryEvent::class);
            $beforeBreadcrumb = $application->make(RedactSentryBreadcrumb::class);
            $options->setBeforeSendCallback($beforeSend);
            $options->setBeforeBreadcrumbCallback($beforeBreadcrumb);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::components([SocialLinks::class]);

        Route::bind('tag', static fn (string $value): Tag => Tag::query()
            ->where('slug->'.App::getLocale(), $value)
            ->firstOrFail());

        // Use a distinct URL so previously cached CSP bundles cannot reach Filament.
        Livewire::setScriptRoute(fn (array $handle, string $path): RoutingRoute => Route::get(
            dirname($path).'/livewire-standard.js',
            $handle,
        ));

        Nightwatch::user(app(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents(app(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands(app(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions(app(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests(app(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries(app(RedactNightwatchQuery::class));
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));

        Event::listen(DiagnosingHealth::class, function (): void {
            $migrations = DB::table('migrations');
            $migrations->limit(1);
            $migrations->exists();

            if (config('health.runtime.enabled') === true) {
                app(RuntimeHealthMonitor::class)->ensureHealthy();
            }
        });

        RateLimiter::for('newsletter', function (Request $request): Limit {
            $ipAddress = $request->ip();
            $limit = Limit::perHour(5);

            return $limit->by($ipAddress);
        });
        RateLimiter::for('search', function (Request $request): Limit {
            if (blank($request->query('q'))) {
                return Limit::none();
            }

            $ipAddress = $request->ip();
            $limit = Limit::perMinute(30);

            return $limit->by($ipAddress);
        });
        RateLimiter::for('newsletter-confirm', function (Request $request): Limit {
            $ipAddress = $request->ip();
            $limit = Limit::perMinute(10);

            return $limit->by($ipAddress);
        });

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
