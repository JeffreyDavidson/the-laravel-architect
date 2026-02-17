# CLAUDE.md
This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Personal website and blog for "The Laravel Architect" — a Laravel 12 application with a Filament v5 admin panel. Content types: blog posts, projects, podcasts/episodes, videos (synced from YouTube), testimonials, and newsletter subscribers.

Live at https://thelaravelarchitect.com. Admin at /admin.

## Common Commands
- Dev server: `composer dev` — runs PHP server, queue worker, Pail logs, and Vite concurrently
- Run all tests: `composer test` (clears config first, then runs `php artisan test`)
- Run a single test: `php artisan test --filter=TestName`
- Lint/format PHP: `./vendor/bin/pint`
- Build frontend: `npm run build`
- Dev frontend only: `npm run dev`
- YouTube sync: `php artisan youtube:sync` (weekly) / `php artisan youtube:stats` (daily)
- Refresh blog content from seeder: `php artisan posts:refresh`

## Architecture

### Frontend (Public Site)
Blade views with Tailwind CSS v4 (via @tailwindcss/vite plugin). Views are in resources/views/ organized by section: blog/, podcast/, projects/, pages/, partials/, components/, layouts/.

Routes are in routes/web.php — standard resource-style controllers, no API routes. Route model binding uses slug field (`{post:slug}`, `{project:slug}`, etc.).

**Dark/Light Mode:** Class-based toggle using `@custom-variant dark (&:where(.dark, .dark *));` in app.css. Without this, Tailwind v4 defaults to `@media (prefers-color-scheme)` and the toggle breaks. Pre-paint script in layout prevents flash. CSS custom properties for theme colors.

**Blade Components (15):** Located in resources/views/components/. Use `<x-svg-icon>` (NOT `<x-icon>` — conflicts with Filament's blade-icons package). Key components: hero-section, page-section, terminal-prompt, ambient-glow, blog-card, button, section-heading, card, social-links, tag-pill, prose, empty-state, coming-soon-badge, post-meta.

### Admin Panel (Filament v5)
Located at /admin. Dark mode is forced. Filament resources follow a directory-per-resource pattern under app/Filament/Resources/:

```
Resources/
  Posts/
    PostResource.php          # Resource definition
    Schemas/PostForm.php      # Form schema (static configure method)
    Tables/PostsTable.php     # Table schema (static configure method)
    Pages/                    # Create, Edit, List pages
```

Each resource separates its form schema and table configuration into dedicated classes with a `public static function configure()` method. Follow this pattern when creating new resources.

Navigation groups: Content, Podcasting, Showcase, Taxonomy, Newsletter, YouTube, Settings.

Custom theme CSS at resources/css/filament/admin/theme.css — cosmetic overrides only. **Do NOT use `!important` on Filament layout elements** (`fi-sidebar`, `fi-topbar`, `fi-main`, `fi-section`) — it breaks the flex layout system. Only override colors, fonts, and decorative properties.

### Filament 5 Gotchas
- `$view` on widgets is **non-static** (instance property, not `protected static`)
- `$navigationIcon` uses `BackedEnum`, `$navigationGroup` uses `UnitEnum`
- `Section` import: `Filament\Schemas\Components\Section`
- `SEO::make()` must be wrapped in `Section::make('SEO')` or Filament can't discover the schema
- `BulkActionGroup`/`DeleteBulkAction` are under `Filament\Actions\*`
- Widget auto-discovery (`discoverWidgets`) + explicit `widgets([])` registration = duplicates. Use one or the other.
- `awcodes/filament-quick-create` namespace is `Awcodes\QuickCreate` (not `Awcodes\FilamentQuickCreate`)
- `pxlrbt/filament-activity-log` is a ServiceProvider, not a Filament Plugin — don't register in plugins array

### Models
All models use `$guarded = []` and auto-generate slugs on creation via `booted()`. **Note:** `booted()` auto-slug does not fire during seeding — add explicit slugs to all seeders.

Common traits:
- HasSEO + getDynamicSEOData() (from `ralphjsmit/laravel-seo`) — on Post, Project, Podcast
- InteractsWithMedia / HasMedia (from `spatie/laravel-medialibrary`) — on Post, Project, Podcast
- HasTags (from `spatie/laravel-tags`) — on Post, Project
- LogsActivity (from `spatie/laravel-activitylog`) — on Post, Project, Testimonial, Episode, Podcast
- Posts and Projects use `scopePublished()` for filtering published content

### Services
app/Services/ contains service classes:
- YouTubeService — YouTube Data API integration. `subscriberCount()` is a static method.
- FeaturedImageGenerator, OgImageGenerator — image generation via Intervention Image

### Key Packages
- Filament v5 — admin panel framework
- Spatie Media Library — file uploads and image conversions
- Spatie Tags — polymorphic tagging
- Spatie Backup — database/file backups
- Spatie Activity Log — model change tracking
- ralphjsmit/laravel-seo — SEO metadata with Filament integration
- Resend — transactional email (hello@thelaravelarchitect.com)
- spatie/laravel-markdown — Markdown rendering for blog content

### Database
SQLite in development (`:memory:` for tests). Tests run against an in-memory SQLite database via PHPUnit configuration.

**SQLite is per-environment** — seeder/DB changes on server don't appear locally and vice versa.

## Deployment
- Hosted on Laravel Forge, server "cold-moon" (DigitalOcean), PHP 8.4
- Cannot SSH to production from dev — use Forge dashboard/terminal
- Forge terminal doesn't support sudo — use PHP settings page for version switching
- `composer.lock` must match PHP version on server (8.4)
- Run `php artisan view:clear` after deploying template changes
- RSS feed is built in RssFeedController (NOT Blade — `<?xml` breaks Blade templates)

## Content Guidelines
- **No em dashes** (` — `) — they're an AI-writing telltale. Use commas, periods, colons, or restructure sentences.
- **No proficiency bars** on tech stack — don't want anyone thinking Jeffrey is weak in something.
- Blog posts use `updateOrCreate` in BlogSeeder so `posts:refresh` command is safe to re-run.
