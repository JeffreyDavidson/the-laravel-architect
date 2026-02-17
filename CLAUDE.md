# CLAUDE.md
This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Personal website and blog for "The Laravel Architect" — a Laravel 12 application with a Filament v5 admin panel. Content types: blog posts, projects, podcasts/episodes, videos (synced from YouTube), testimonials, and newsletter subscribers.

## Common Commands
- Dev server: composer dev — runs PHP server, queue worker, Pail logs, and Vite concurrently
- Run all tests: composer test (clears config first, then runs `php artisan test`)
- Run a single test: php artisan test --filter=TestName
- Lint/format PHP: ./vendor/bin/pint
- Build frontend: npm run build
- Dev frontend only: npm run dev
- YouTube sync: php artisan youtube:sync (weekly) / php artisan youtube:stats (daily)

## Architecture

### Frontend (Public Site)
Blade views with Tailwind CSS v4 (via @tailwindcss/vite plugin). Views are in resources/views/ organized by section: blog/, podcast/, projects/, pages/, partials/, components/, layouts/.

Routes are in routes/web.php — standard resource-style controllers, no API routes. Route model binding uses slug field (`{post:slug}`, {project:slug}, etc.).

### Admin Panel (Filament v5)
Located at /admin. Filament resources follow a directory-per-resource pattern under app/Filament/Resources/:

Resources/
  Posts/
    PostResource.php          # Resource definition
    Schemas/PostForm.php      # Form schema (static configure method)
    Tables/PostsTable.php     # Table schema (static configure method)
    Pages/                    # Create, Edit, List pages

Each resource separates its form schema and table configuration into dedicated classes with a public static function configure() method. Follow this pattern when creating new resources.

Navigation groups: Content, Podcasting, Showcase, Taxonomy, Newsletter.

### Models
All models use $guarded = [] and auto-generate slugs on creation via booted(). Common traits:
- HasSEO + getDynamicSEOData() (from `ralphjsmit/laravel-seo`) — on Post, Project, Podcast
- InteractsWithMedia / HasMedia (from `spatie/laravel-medialibrary`) — on Post, Project, Podcast
- HasTags (from `spatie/laravel-tags`) — on Post, Project
- Posts and Projects use scopePublished() for filtering published content

### Services
app/Services/ contains service classes for external integrations and image generation:
- YouTubeService — YouTube Data API integration for syncing videos
- FeaturedImageGenerator, OgImageGenerator — image generation via Intervention Image

### Key Packages
- Filament v5 — admin panel framework
- Spatie Media Library — file uploads and image conversions
- Spatie Tags — polymorphic tagging
- ralphjsmit/laravel-seo — SEO metadata with Filament integration
- Resend — transactional email
- spatie/laravel-markdown — Markdown rendering for blog content

### Database
SQLite in development (`:memory:` for tests). Tests run against an in-memory SQLite database via PHPUnit configuration.
