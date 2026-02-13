<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// Podcasts
Route::get('/podcasts', [PodcastController::class, 'index'])->name('podcast.index');
Route::get('/podcasts/{podcast:slug}', [PodcastController::class, 'show'])->name('podcast.show');
Route::get('/podcasts/{podcast:slug}/{episode:slug}', [PodcastController::class, 'episode'])->name('podcast.episode');

// Projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
