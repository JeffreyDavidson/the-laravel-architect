<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Videos\VideoResource;
use Awcodes\QuickCreate\QuickCreatePlugin;
use Awcodes\Versions\VersionsPlugin;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
// AccountWidget replaced by WelcomeWidget
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->multiFactorAuthentication(AppAuthentication::make(), isRequired: fn (): bool => app()->isProduction())
            ->colors([
                'primary' => Color::hex('#4a7fbf'),
                'gray' => Color::hex('#1a1d21'),
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->darkMode(isForced: true)
            ->brandName('The Laravel Architect')
            ->brandLogo(asset('images/logo-color.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/logo-color.svg'))
            ->font('Inter')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Content')->icon(Heroicon::OutlinedDocumentText),
                NavigationGroup::make('Podcasting')->icon(Heroicon::OutlinedMicrophone),
                NavigationGroup::make('Showcase')->icon(Heroicon::OutlinedCodeBracket),
                NavigationGroup::make('Taxonomy')->icon(Heroicon::OutlinedTag),
                NavigationGroup::make('Newsletter')->icon(Heroicon::OutlinedEnvelope),
            ])
            ->plugins([
                FilamentSpatieLaravelBackupPlugin::make(),
                QuickCreatePlugin::make()
                    ->excludes([
                        SubscriberResource::class,
                        VideoResource::class,
                    ]),
                VersionsPlugin::make(),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('View Site')
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt),
                MenuItem::make()
                    ->label('GitHub')
                    ->url('https://github.com/JeffreyDavidson/the-laravel-architect', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedCodeBracket),
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_AFTER,
                fn (): HtmlString => new HtmlString('<div class="tla-admin-rail" aria-hidden="true"></div>'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): HtmlString => new HtmlString('
                    <div class="text-center mb-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">$ php artisan login</p>
                    </div>
                '),
            )
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
