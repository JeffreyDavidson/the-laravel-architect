<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogTagController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Controllers\NewsletterConfirmedController;
use App\Http\Controllers\NewsletterIssueController;
use App\Http\Controllers\NewsletterOneClickUnsubscriptionController;
use App\Http\Controllers\NewsletterRssFeedController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscriptionController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\PodcastEpisodeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PreviewEpisodeController;
use App\Http\Controllers\PreviewNewsletterIssueController;
use App\Http\Controllers\PreviewPostController;
use App\Http\Controllers\PreviewProjectController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResendWebhookController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UsesController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationLink;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Pages
Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/services', ServiceController::class)->name('services');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::get('/privacy', PrivacyController::class)->name('privacy');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-form')
    ->name('contact.store');
Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])
    ->middleware('throttle:newsletter')
    ->name('newsletter.subscribe');
// The middleware checks the signature and token; an unusable link, including
// one whose subscriber was pruned, goes back to the signup form.
Route::middleware([EnsureValidNewsletterConfirmationLink::class, 'throttle:newsletter-confirm'])
    ->missing(fn (): RedirectResponse => EnsureValidNewsletterConfirmationLink::redirectToSignupForm())
    ->group(function (): void {
        // Not stored anywhere, so the back button reloads the page instead of restoring
        // a form already submitted, and the CSRF token and email are never cached.
        Route::get('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'create'])
            ->middleware('cache.headers:no_store;private')
            ->name('newsletter.confirm');
        Route::post('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'store'])
            ->name('newsletter.confirm.store');
    });
Route::get('/newsletter/confirmed', NewsletterConfirmedController::class)->name('newsletter.confirmed');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterUnsubscriptionController::class, 'create'])
    ->middleware(['signed', 'throttle:newsletter-unsubscribe'])
    ->name('newsletter.unsubscribe');
Route::delete('/newsletter/unsubscribe/{subscriber}', [NewsletterSubscriptionController::class, 'destroy'])
    ->middleware(['signed', 'throttle:newsletter-unsubscribe'])
    ->name('newsletter.unsubscribe.store');
Route::post('/newsletter/unsubscribe/{subscriber}', NewsletterOneClickUnsubscriptionController::class)
    ->middleware(['signed', 'throttle:newsletter-unsubscribe'])
    ->name('newsletter.unsubscribe.oneClick');
Route::get('/newsletter', [NewsletterIssueController::class, 'index'])->name('newsletter.index');
// Feed readers poll this: no session, so a hit writes no session row or cookie.
Route::get('/newsletter/rss', NewsletterRssFeedController::class)
    ->withoutMiddleware('web')
    ->name('newsletter.rss');
Route::get('/newsletter/{newsletterIssue:slug}', [NewsletterIssueController::class, 'show'])->name('newsletter.issue');
Route::get('/uses', UsesController::class)->name('uses');
Route::get('/search', SearchController::class)
    ->middleware('throttle:search')
    ->name('search');
Route::get('/archive', ArchiveController::class)->name('archive.index');

// Signed content previews
Route::middleware('signed')
    ->prefix('preview')
    ->group(function (): void {
        Route::get('/posts/{post:slug}', PreviewPostController::class)->name('preview.post');
        Route::get('/projects/{project:slug}', PreviewProjectController::class)->name('preview.project');
        Route::get('/episodes/{episode:slug}', PreviewEpisodeController::class)->name('preview.episode');
        Route::get('/newsletter/{newsletterIssue:slug}', PreviewNewsletterIssueController::class)->name('preview.newsletter-issue');
    });

// RSS & Sitemap
// Feed readers and crawlers poll /rss and /sitemap.xml: no session, so a hit writes no session row or cookie.
Route::get('/rss', RssFeedController::class)
    ->withoutMiddleware('web')
    ->name('rss');
// Served like a static file (no session cookies, publicly cacheable) so the CDN keeps it.
Route::get('/robots.txt', RobotsController::class)
    ->withoutMiddleware('web')
    ->name('robots');
// Server-to-server: no session, cookies or forgery token; the Resend signature is the credential.
Route::post('/webhooks/resend', ResendWebhookController::class)
    ->withoutMiddleware('web')
    ->middleware('throttle:resend-webhook')
    ->name('webhooks.resend');
Route::get('/sitemap.xml', SitemapController::class)
    ->withoutMiddleware('web')
    ->name('sitemap');

// Blog
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', BlogCategoryController::class)->name('blog.category');
Route::get('/blog/tag/{tag:slug}', BlogTagController::class)->name('blog.tag');

// OG Images
// Fetched by social crawlers: no session row or cookie. Excluding the whole `web` group
// would also drop the {post:slug} binding, so only the session middleware is removed.
Route::get('/og-image/{post:slug}', OgImageController::class)
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
    ])
    ->name('og-image');

// Podcasts
Route::get('/podcasts', [PodcastController::class, 'index'])->name('podcast.index');
Route::get('/podcasts/{podcast:slug}', [PodcastController::class, 'show'])->name('podcast.show');
Route::get('/podcasts/{podcast:slug}/{episode:slug}', PodcastEpisodeController::class)->name('podcast.episode');

// Projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
