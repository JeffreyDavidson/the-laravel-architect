<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Tag;
use App\Models\User;
use App\Services\PublicPageBenchmark;
use App\View\Components\SocialLinks;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RalphJSmit\Laravel\SEO\Facades\SEOManager;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PublicPageBenchmark::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        DB::prohibitDestructiveCommands(app()->isProduction());

        Blade::components([SocialLinks::class]);

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

        $this->configureAuthorization();
        $this->configureRateLimiting();
        $this->configureUrls();
    }

    /**
     * The site has a single administrator role, so one app-wide gate replaces
     * per-model policies: administrators may perform every ability and everyone
     * else is denied. Global before-callbacks run for every ability whether or
     * not a policy method exists, and Filament honors them. Capability limits,
     * such as subscribers never being created in the panel, live on the
     * resources, because this gate cannot restrict an administrator.
     *
     * Non-administrators get `false`, not `null`: there are no policies, and
     * Filament allows a resource ability that has no policy unless a
     * before-callback denies it, so `null` would let non-administrators pass
     * every resource check behind the panel's access gate.
     */
    private function configureAuthorization(): void
    {
        Gate::before(fn (User $user): bool => $user->is_admin);
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
