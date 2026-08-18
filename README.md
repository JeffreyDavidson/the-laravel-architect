# The Laravel Architect

The personal website, blog, portfolio, podcast archive, and newsletter for Jeffrey Davidson. The application uses Laravel 13, Blade, Tailwind CSS 4, and a Filament 5 administration panel.

## Requirements

- PHP 8.4 or newer with SQLite and GD support
- Composer
- Node.js 22 and npm

## Local setup

```bash
composer setup
composer dev
```

The setup script installs PHP and JavaScript dependencies, creates `.env`, generates an application key, runs migrations, and builds assets. The development command starts Laravel, the queue worker, Pail, and Vite.

## Quality checks

```bash
composer test
composer test:types
vendor/bin/pint --test
npm run build
composer audit --locked
npm audit --omit=dev
```

CI runs dependency auditing, formatting, static analysis, asset compilation, and the Pest suite on every pull request and push to `develop` or `main`.

## Architecture

Public pages are server-rendered Blade views. Route-model binding uses content slugs, while publication scopes keep drafts and future content off public pages, feeds, and the sitemap.

The Filament panel is available at `/admin`. Panel admission requires an approved role, and app-based multi-factor authentication is required in production. Filament Shield manages resource permissions.

Uploaded images and audio are stored through Laravel's `public` filesystem disk. Models store explicit file paths; run `php artisan storage:link` on a new environment.

Newsletter subscriptions use a signed, expiring double-opt-in link. Contact, newsletter, and testimonial submissions include abuse controls. Content changes are recorded with Spatie Activity Log.

## Scheduled work

The production scheduler must run every minute. It dispatches:

- `youtube:stats` daily
- `youtube:sync` weekly
- Filament Excel pruning daily

YouTube tasks prevent overlapping execution. The homepage caches the subscriber count and retains the last successful value when YouTube is unavailable.

## Deployment

The application is hosted through Laravel Forge. A deployment should install locked dependencies, build production assets, run forward-only migrations, refresh optimized caches, and ensure the scheduler and queue worker are active.

The media migration copies existing Media Library associations to native path columns before dropping the package table. Back up the database and `storage/app/public` before deploying that migration.
