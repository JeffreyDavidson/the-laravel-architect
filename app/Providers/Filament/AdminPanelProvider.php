<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Posts\PostResource;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\UserMenuPosition;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JeffreyDavidson\CreatorKit\Support\Time\DisplayTimezone;

final class AdminPanelProvider extends PanelProvider
{
    /** Panel dates and times display in the site's timezone, not the UTC the database stores. */
    public function boot(): void
    {
        FilamentTimezone::set(DisplayTimezone::name(...));
    }

    public function panel(Panel $panel): Panel
    {
        CreateRecord::stickyFormActions();
        EditRecord::stickyFormActions();

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->spa(hasPrefetching: true)
            ->userMenu(position: UserMenuPosition::Topbar)
            ->multiFactorAuthentication(
                AppAuthentication::make(),
                isRequired: fn (): bool => app()->isProduction(),
            )
            ->colors([
                'primary' => Color::hex('#4a7fbf'),
                'gray' => Color::hex('#1a1d21'),
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->darkMode()
            ->themeSwitcher()
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->brandName('The Laravel Architect')
            ->brandLogo('/images/elephant-companion-128.webp')
            ->brandLogoHeight('2.5rem')
            ->favicon('/images/favicon-32x32.png')
            ->font('IBM Plex Sans', provider: LocalFontProvider::class)
            ->monoFont('IBM Plex Mono', provider: LocalFontProvider::class)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->userMenuItems([
                MenuItem::make()
                    ->label('View Site')
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedGlobeAlt),
                MenuItem::make()
                    ->label('GitHub')
                    ->url(config()->string('app.repository_url'), shouldOpenInNewTab: true)
                    ->icon(Heroicon::OutlinedCodeBracket),
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): View => view('filament.sidebar.new-post-link', ['url' => PostResource::getUrl('create')]),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): View => view('filament.auth.login-kicker'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.auth.theme-switcher'),
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (Vite $vite): HtmlString => $vite('resources/js/filament/admin.js'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
