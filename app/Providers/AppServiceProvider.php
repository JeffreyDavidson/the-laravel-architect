<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
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
        FilamentView::registerRenderHook(
            "panels::body.end",
            fn () => Blade::render(<<<HTML
                <script>
                    document.addEventListener("livewire:navigating", () => {
                        const sidebar = document.querySelector(".fi-sidebar-nav");
                        if (sidebar) window.__sidebarScroll = sidebar.scrollTop;
                    });
                    document.addEventListener("livewire:navigated", () => {
                        const sidebar = document.querySelector(".fi-sidebar-nav");
                        if (sidebar && window.__sidebarScroll) sidebar.scrollTop = window.__sidebarScroll;
                    });
                </script>
            HTML),
        );
    }
}
