<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\EditorialCalendar;
use App\Filament\Pages\Insights;
use App\Filament\Pages\MediaHealth;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Videos\VideoResource;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\UserMenuPosition;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Vite;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
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
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => $builder
                ->items([
                    ...Dashboard::getNavigationItems(),
                ])
                ->groups([
                    NavigationGroup::make('Publish')
                        ->items([
                            ...EditorialCalendar::getNavigationItems(),
                            ...PostResource::getNavigationItems(),
                            ...PodcastResource::getNavigationItems(),
                            ...EpisodeResource::getNavigationItems(),
                            ...NewsletterIssueResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Library')
                        ->items([
                            ...ProjectResource::getNavigationItems(),
                            ...CategoryResource::getNavigationItems(),
                            ...TagResource::getNavigationItems(),
                            ...VideoResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Audience')
                        ->items([
                            ...SubscriberResource::getNavigationItems(),
                            ...ContactInquiryResource::getNavigationItems(),
                        ]),
                    NavigationGroup::make('Operations')
                        ->items([
                            ...Insights::getNavigationItems(),
                            ...MediaHealth::getNavigationItems(),
                        ]),
                ]))
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
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): HtmlString => new HtmlString(sprintf(
                    '<a class="tla-sidebar-primary" href="%s" aria-label="New post" title="New post"><span class="tla-sidebar-primary__icon" aria-hidden="true">+</span><span class="tla-sidebar-primary__label">New post</span></a>',
                    e(PostResource::getUrl('create')),
                )),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): HtmlString => new HtmlString('
                    <div class="tla-auth-kicker" aria-hidden="true">
                        <span class="tla-auth-kicker__dot"></span>
                        Private studio access
                    </div>
                '),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): HtmlString => new HtmlString(view('filament.auth.theme-switcher')->render()),
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
