<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Podcasts\PodcastResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Tags\TagResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Resources\Videos\VideoResource;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationBuilder;
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
            ->spa(hasPrefetching: true)
            ->userMenu(position: UserMenuPosition::Topbar)
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
            ->brandLogo('/images/logo-color-128.webp')
            ->brandLogoHeight('2.5rem')
            ->favicon('/images/favicon-32x32.png')
            ->font('Inter')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        ...Dashboard::getNavigationItems(),
                        ...TestimonialResource::getNavigationItems(),
                    ])
                    ->groups([
                        NavigationGroup::make('Content')
                            ->collapsible(false)
                            ->items([
                                ...PostResource::getNavigationItems(),
                                ...CategoryResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Podcasting')
                            ->collapsible(false)
                            ->items([
                                ...PodcastResource::getNavigationItems(),
                                ...EpisodeResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Showcase')
                            ->collapsible(false)
                            ->items([
                                ...ProjectResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Taxonomy')
                            ->collapsible(false)
                            ->items([
                                ...TagResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('Newsletter')
                            ->collapsible(false)
                            ->items([
                                ...SubscriberResource::getNavigationItems(),
                            ]),
                        NavigationGroup::make('YouTube')
                            ->collapsible(false)
                            ->items([
                                ...VideoResource::getNavigationItems(),
                            ]),
                    ]);
            })
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
                    '<a class="tla-sidebar-primary" href="%s"><span aria-hidden="true">+</span><span>New post</span></a>',
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
