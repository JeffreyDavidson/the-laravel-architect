<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TestimonialController;
use App\Http\Middleware\EnsureValidNewsletterConfirmationToken;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{subscriber}/{token}', [NewsletterController::class, 'showConfirmation'])->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{subscriber}/{token}', [NewsletterController::class, 'confirm'])->middleware(['signed', EnsureValidNewsletterConfirmationToken::class, 'throttle:newsletter-confirm'])->name('newsletter.confirm.store');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'showUnsubscribe'])->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])->middleware(['signed', 'throttle:newsletter-confirm'])->name('newsletter.unsubscribe.store');
Route::get('/testimonials/submit', [TestimonialController::class, 'create'])->name('testimonials.create');
Route::post('/testimonials', [TestimonialController::class, 'store'])->middleware('throttle:testimonials')->name('testimonials.store');
Route::get('/uses', [PageController::class, 'uses'])->name('uses');

// RSS & Sitemap
Route::get('/rss', RssFeedController::class)->name('rss');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// OG Images
Route::get('/og-image/{post:slug}', OgImageController::class)->name('og-image');

// Podcasts
Route::get('/podcasts', [PodcastController::class, 'index'])->name('podcast.index');
Route::get('/podcasts/{podcast:slug}', [PodcastController::class, 'show'])->name('podcast.show');
Route::get('/podcasts/{podcast:slug}/{episode:slug}', [PodcastController::class, 'episode'])->name('podcast.episode');

// Projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
