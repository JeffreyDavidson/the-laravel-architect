<?php

namespace App\Providers;

use App\Support\RuntimeHealth;
use Filament\Support\Facades\FilamentView;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(DiagnosingHealth::class, function (): void {
            DB::table('migrations')->limit(1)->exists();

            if (config('health.runtime.enabled') === true) {
                app(RuntimeHealth::class)->ensureHealthy();
            }
        });

        RateLimiter::for('newsletter', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('newsletter-confirm', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('testimonials', fn (Request $request) => Limit::perHour(3)->by($request->ip()));

        $appUrl = config('app.url');

        if (is_string($appUrl) && str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => Blade::render(<<<'HTML'
                <script>
                    const expandSidebarGroups = () => {
                        window.localStorage.removeItem('collapsedGroups');

                        document.querySelectorAll('.fi-sidebar-group').forEach((group) => {
                            group.classList.remove('fi-collapsed');

                            const items = group.querySelector('.fi-sidebar-group-items');

                            if (! items) return;

                            items.style.display = '';
                            items.style.height = '';
                            items.style.maxHeight = '';
                        });

                        const sidebar = window.Alpine?.store('sidebar');

                        if (sidebar) sidebar.collapsedGroups = [];
                    };

                    document.addEventListener('alpine:initialized', () => {
                        window.requestAnimationFrame(expandSidebarGroups);
                    }, { once: true });

                    document.addEventListener('livewire:navigated', () => {
                        window.requestAnimationFrame(expandSidebarGroups);
                    });

                    document.addEventListener('livewire:navigating', () => {
                        const sidebar = document.querySelector('.fi-sidebar-nav');
                        if (sidebar) window.__sidebarScroll = sidebar.scrollTop;
                    });
                    document.addEventListener('livewire:navigated', () => {
                        const sidebar = document.querySelector('.fi-sidebar-nav');
                        if (sidebar && window.__sidebarScroll) sidebar.scrollTop = window.__sidebarScroll;
                    });
                </script>
            HTML),
        );
    }
}
