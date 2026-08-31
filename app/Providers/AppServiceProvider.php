<?php

namespace App\Providers;

use App\Monitoring\RedactNightwatchRequest;
use App\Services\RuntimeHealthMonitor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Nightwatch::user(fn (Authenticatable $user): array => []);
        Nightwatch::redactRequests(app(RedactNightwatchRequest::class));

        Event::listen(DiagnosingHealth::class, function (): void {
            DB::table('migrations')->limit(1)->exists();

            if (config('health.runtime.enabled') === true) {
                app(RuntimeHealthMonitor::class)->ensureHealthy();
            }
        });

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('newsletter-confirm', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('testimonials', fn (Request $request) => Limit::perHour(3)->by($request->ip()));

        $appUrl = config('app.url');

        if (is_string($appUrl) && str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
