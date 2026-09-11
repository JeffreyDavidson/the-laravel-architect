<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogTagController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterConfirmationController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscriptionController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\PodcastEpisodeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UsesController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationToken;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/services', ServiceController::class)->name('services');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::get('/privacy', PrivacyController::class)->name('privacy');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');
Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'create'])->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{subscriber}/{token}', [NewsletterConfirmationController::class, 'store'])->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm.store');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterUnsubscriptionController::class, 'create'])->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe');
Route::delete('/newsletter/unsubscribe/{subscriber}', [NewsletterSubscriptionController::class, 'destroy'])->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe.store');
Route::get('/uses', UsesController::class)->name('uses');

// RSS & Sitemap
Route::get('/rss', RssFeedController::class)->name('rss');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Blog
Route::get('/blog', [PostController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', BlogCategoryController::class)->name('blog.category');
Route::get('/blog/tag/{tag:slug}', BlogTagController::class)->name('blog.tag');

// OG Images
Route::get('/og-image/{post:slug}', OgImageController::class)->name('og-image');

// Podcasts
Route::get('/podcasts', [PodcastController::class, 'index'])->name('podcast.index');
Route::get('/podcasts/{podcast:slug}', [PodcastController::class, 'show'])->name('podcast.show');
Route::get('/podcasts/{podcast:slug}/{episode:slug}', PodcastEpisodeController::class)->name('podcast.episode');

// Projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
