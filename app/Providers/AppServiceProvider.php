<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
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
                    document.addEventListener('alpine:init', () => {
                        // Navigation groups are intentionally non-collapsible in this panel.
                        // Clear stale Filament state from sessions that used collapsible groups.
                        window.localStorage.removeItem('collapsedGroups');
                    }, { once: true });

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
