<?php

namespace App\Providers;

use App\Models\Tag;
use App\Services\Health\RuntimeHealthMonitor;
use App\Services\PublicPageBenchmark;
use App\Support\DisplayTimezone;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCacheEvent;
use App\Support\Monitoring\Nightwatch\RedactNightwatchCommand;
use App\Support\Monitoring\Nightwatch\RedactNightwatchException;
use App\Support\Monitoring\Nightwatch\RedactNightwatchOutgoingRequest;
use App\Support\Monitoring\Nightwatch\RedactNightwatchQuery;
use App\Support\Monitoring\Nightwatch\RedactNightwatchRequest;
use App\Support\Monitoring\Nightwatch\ResolveNightwatchUser;
use App\Support\Monitoring\Sentry\RedactSentryBreadcrumb;
use App\Support\Monitoring\Sentry\RedactSentryEvent;
use App\View\Components\SocialLinks;
use App\View\Composers\StructuredDataComposer;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\RedirectResponse;
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
use Laravel\Nightwatch\Facades\Nightwatch;
use Livewire\Livewire;
use RalphJSmit\Laravel\SEO\Facades\SEOManager;
use RalphJSmit\Laravel\SEO\Support\SEOData;
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
        Model::preventLazyLoading(! app()->isProduction());
        DB::prohibitDestructiveCommands(app()->isProduction());

        Blade::components([SocialLinks::class]);

        FilamentTimezone::set(DisplayTimezone::name(...));

        SEOManager::SEODataTransformer(function (SEOData $seoData): SEOData {
            $seoData->locale = config()->string('seo.og_locale');

            return $seoData;
        });

        Route::bind('tag', static fn (string $value): Tag => Tag::query()
            ->where('slug->'.App::getLocale(), $value)
            ->firstOrFail());

        // Use a distinct URL so previously cached CSP bundles cannot reach Filament.
        Livewire::setScriptRoute(fn (array $handle, string $path): RoutingRoute => Route::get(
            dirname($path).'/livewire-standard.js',
            $handle,
        ));

        $this->configureNightwatch();
        $this->configureHealthChecks();
        $this->configureRateLimiting();
        $this->configureUrls();

        View::composer([
            'errors.404',
            'pages.*',
        ], StructuredDataComposer::class);
    }

    private function configureNightwatch(): void
    {
        Nightwatch::user(app(ResolveNightwatchUser::class));
        Nightwatch::redactCacheEvents(app(RedactNightwatchCacheEvent::class));
        Nightwatch::redactCommands(app(RedactNightwatchCommand::class));
        Nightwatch::redactExceptions(app(RedactNightwatchException::class));
        Nightwatch::redactOutgoingRequests(app(RedactNightwatchOutgoingRequest::class));
        Nightwatch::redactQueries(app(RedactNightwatchQuery::class));
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));
    }

    private function configureHealthChecks(): void
    {
        Event::listen(DiagnosingHealth::class, function (): void {
            $migrations = DB::table('migrations');
            $migrations->limit(1);
            $migrations->exists();

            if (config('health.runtime.enabled') === true) {
                app(RuntimeHealthMonitor::class)->ensureHealthy();
            }
        });
    }

    private function configureRateLimiting(): void
    {
        // Only a sent message counts toward the hourly limit, so typos and failed checks never
        // lock out a visitor. Every attempt counts toward the looser per-minute limit, which
        // caps the blocking Turnstile verification calls junk submissions can trigger.
        RateLimiter::for('contact-form', function (Request $request): array {
            $ipAddress = $request->ip();
            $sentMessages = Limit::perHour(3)
                ->by("sent:{$ipAddress}")
                ->after(fn (): bool => blank($request->input('website')) && session()->has('success'))
                ->response(fn (): RedirectResponse => back()
                    ->withErrors(['message' => 'Too many submissions. Please try again later.'])
                    ->withInput($request->except(['website', 'cf-turnstile-response'])));
            $attempts = Limit::perMinute(10)
                ->by("attempts:{$ipAddress}");

            return [$sentMessages, $attempts];
        });
        RateLimiter::for('newsletter', function (Request $request): Limit {
            $ipAddress = $request->ip();
            $limit = Limit::perHour(5);

            return $limit->by($ipAddress);
        });
        // Half of Resend's default team limit, leaving room for contact mail.
        RateLimiter::for('newsletter-delivery', fn (): Limit => Limit::perSecond(5));
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
        // Mail providers send one-click unsubscribes from a few shared addresses, often in a
        // burst after a send. The signed link already authorizes them, so this only caps abuse.
        RateLimiter::for('newsletter-unsubscribe', function (Request $request): Limit {
            $ipAddress = $request->ip();
            $limit = Limit::perMinute(120);

            return $limit->by($ipAddress);
        });
        RateLimiter::for('resend-webhook', function (Request $request): Limit {
            $ipAddress = $request->ip();
            $limit = Limit::perMinute(60);

            return $limit->by($ipAddress);
        });
    }

    /** Absolute links, such as newsletter confirmations, must never follow a spoofed Host header. */
    private function configureUrls(): void
    {
        $appUrl = config()->string('app.url');

        if (app()->isProduction()) {
            URL::forceRootUrl($appUrl);
        }

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
