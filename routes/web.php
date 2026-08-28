<?php

use App\Http\Controllers\ConfirmNewsletterSubscriptionController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\PodcastsController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\ShowAboutController;
use App\Http\Controllers\ShowBlogCategoryController;
use App\Http\Controllers\ShowBlogTagController;
use App\Http\Controllers\ShowContactController;
use App\Http\Controllers\ShowHomeController;
use App\Http\Controllers\ShowNewsletterConfirmationController;
use App\Http\Controllers\ShowNewsletterUnsubscribeController;
use App\Http\Controllers\ShowPodcastEpisodeController;
use App\Http\Controllers\ShowPrivacyController;
use App\Http\Controllers\ShowSitemapController;
use App\Http\Controllers\ShowTestimonialSubmissionController;
use App\Http\Controllers\ShowUsesController;
use App\Http\Controllers\SubmitContactController;
use App\Http\Controllers\SubmitTestimonialController;
use App\Http\Controllers\SubscribeNewsletterController;
use App\Http\Controllers\UnsubscribeNewsletterController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationToken;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', ShowHomeController::class)->name('home');
Route::get('/about', ShowAboutController::class)->name('about');
Route::get('/contact', ShowContactController::class)->name('contact');
Route::get('/privacy', ShowPrivacyController::class)->name('privacy');
Route::post('/contact', SubmitContactController::class)->name('contact.submit');
Route::post('/newsletter', SubscribeNewsletterController::class)->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{subscriber}/{token}', ShowNewsletterConfirmationController::class)->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{subscriber}/{token}', ConfirmNewsletterSubscriptionController::class)->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm.store');
Route::get('/newsletter/unsubscribe/{subscriber}', ShowNewsletterUnsubscribeController::class)->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{subscriber}', UnsubscribeNewsletterController::class)->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe.store');
Route::get('/testimonials/submit', ShowTestimonialSubmissionController::class)->name('testimonials.create');
Route::post('/testimonials', SubmitTestimonialController::class)->middleware('throttle:testimonials')->name('testimonials.store');
Route::get('/uses', ShowUsesController::class)->name('uses');

// RSS & Sitemap
Route::get('/rss', RssFeedController::class)->name('rss');
Route::get('/sitemap.xml', ShowSitemapController::class)->name('sitemap');

// Blog
Route::get('/blog', [PostsController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [PostsController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', ShowBlogCategoryController::class)->name('blog.category');
Route::get('/blog/tag/{tag:slug}', ShowBlogTagController::class)->name('blog.tag');

// OG Images
Route::get('/og-image/{post:slug}', OgImageController::class)->name('og-image');

// Podcasts
Route::get('/podcasts', [PodcastsController::class, 'index'])->name('podcast.index');
Route::get('/podcasts/{podcast:slug}', [PodcastsController::class, 'show'])->name('podcast.show');
Route::get('/podcasts/{podcast:slug}/{episode:slug}', ShowPodcastEpisodeController::class)->name('podcast.episode');

// Projects
Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectsController::class, 'show'])->name('projects.show');
