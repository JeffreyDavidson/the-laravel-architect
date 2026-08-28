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

To bootstrap an administrator on a fresh environment, create the Filament user before running the database seeder so the seeder preserves the password you choose and marks the existing account as an administrator:

```bash
php artisan make:filament-user --panel=admin --email=admin@example.test
php artisan db:seed
```

The email passed to the Filament command must match `ADMIN_EMAIL`. Seeders generate unknown random passwords when they must create a user, and they never reset an existing user's password. Set private production values for `ADMIN_EMAIL` and `CONTENT_AUTHOR_EMAIL` before seeding production data.

## Quality checks

```bash
composer test
composer test:types
composer test:filament
vendor/bin/pint --test
npm run build
npm run test:assets
composer audit --locked
npm audit --omit=dev
```

CI runs dependency auditing, formatting, static analysis, asset compilation and budget checks for the public and admin bundles, Playwright browser checks, and the Pest suite on every pull request and push to `develop` or `main`. Superseded runs are cancelled, and failed browser checks retain screenshots and traces for seven days.

## Architecture

Public pages are server-rendered Blade views. Controllers pass page-specific `SEOData` or an SEO-enabled content model to the shared layout, which renders titles, descriptions, canonical links, social metadata, and robots directives. The shared JSON-LD graph uses named Laravel routes for canonical site, author, static-page, article, podcast, episode, project case-study, collection, item-list, and breadcrumb entities. Paginated public archives reject out-of-range pages and use page-specific titles, descriptions, canonical and collection URLs, and continuous item positions. Route-model binding uses content slugs, while publication scopes keep drafts and future content off public pages, feeds, and the sitemap. Signed newsletter action pages and the testimonial submission form are explicitly excluded from indexing.

Public interaction modules live in `resources/js`, shared presentation rules live in `resources/css`, and both are compiled through Vite. Compressed budgets guard the public entry points and lazy homepage scene in CI. The Filament theme is a separate budgeted Vite entry loaded only by the admin panel. Editorial images that participate in the build live in `resources/images` and are referenced with `Vite::asset()`. Files that must retain a stable direct URL for browsers or third-party consumers, such as favicons and Filament branding, remain in `public`. The asset-budget check rejects unexpected files in `public/images`, so new images must either use the Vite pipeline or be intentionally added to the direct-URL allowlist.

Generated post OG images are cached on the private local filesystem. Cache validity is based on the rendered title, category name, and an explicit renderer version; deleting a post removes its cached image.

The Filament panel is available at `/admin`. Panel admission requires the native `is_admin` flag, resource actions are protected by Laravel policies, and app-based multi-factor authentication is required in production.

Uploaded images and audio are validated and stored through Laravel's `public` filesystem disk. Models store explicit file paths and remove replaced or record-owned files; deleting a podcast also removes media owned by its database-cascaded episodes. Project and post featured images and podcast cover images retain their original upload as the canonical fallback and generate 640px and 1280px WebP variants for responsive public rendering. Bundled podcast cover fallbacks provide 128px, 320px, and 512px Vite-managed variants for smaller episode artwork. Replacing or deleting an uploaded image also removes its variants. Run `php artisan storage:link` on a new environment, then use `php artisan projects:generate-image-variants`, `php artisan posts:generate-image-variants`, and `php artisan podcasts:generate-image-variants` when backfilling existing uploads.

Newsletter subscriptions use a signed, expiring double-opt-in link followed by an explicit confirmation form, preventing link scanners from changing subscriber state. Subscriber-specific signed unsubscribe links use the same explicit form pattern and should be included in every newsletter. Contact, newsletter, and testimonial submissions include abuse controls. Content changes are recorded with Spatie Activity Log.

Application responses set a constrained Content Security Policy plus clickjacking, transport-security, MIME-sniffing, referrer, and browser-feature policy headers globally. The policy limits document embedding, object sources, and base URLs without interfering with Filament or Vite's inline runtime behavior.

The `/up` health endpoint verifies both the Laravel runtime and access to the migrated application database. Production monitoring should treat any non-200 response as unhealthy.

## Scheduled work

The production scheduler must run every minute. It dispatches:

- a runtime heartbeat every minute to verify both the scheduler and queue worker
- application and database backups daily
- backup cleanup weekly
- backup health monitoring daily
- failed-job monitoring hourly and failed-job pruning daily
- `youtube:stats` daily
- `youtube:sync` weekly

YouTube tasks prevent overlapping execution. The homepage caches the subscriber count, retains the last successful value when YouTube is unavailable or returns malformed data, and displays the latest published videos from the local sync instead of date-sensitive promotional placeholders.

Production must set `DB_DATABASE` to the absolute path of the live SQLite database and `BACKUP_MEDIA_PATH` to the absolute path of the persistent public-media directory. Set `BACKUP_DISKS=local,nas-backups`, configure the `BACKUP_SFTP_*` values and the Flysystem-compatible fingerprint derived from an independently verified SSH host key, and set `BACKUP_ARCHIVE_PASSWORD` before enabling off-server backups. `MAIL_CONTACT_TO` and `BACKUP_NOTIFICATION_EMAIL` must point to monitored mailboxes.

Create a Cloudflare Turnstile widget for the production host and set `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `TURNSTILE_CONTACT_ACTION=contact-form`, and `TURNSTILE_ALLOWED_HOSTNAMES=thelaravelarchitect.com,www.thelaravelarchitect.com`. Contact submissions fail closed when verification is unavailable or the verified action and hostname do not match the configured values.

Set `RUNTIME_HEALTH_ENABLED=true` in production. The scheduler records its heartbeat and dispatches a queued probe every minute; `/up` returns an unhealthy response when either heartbeat is older than `RUNTIME_HEALTH_MAX_AGE` seconds.

Set `QUEUE_FAILED_JOB_ALERT_THRESHOLD` to the number of retained failures that operations will tolerate and `QUEUE_FAILED_JOB_RETENTION_HOURS` to the retention window. The scheduled monitor emails the backup notification recipient when the threshold is exceeded.

After changing backup configuration, run `php artisan backup:run`, `php artisan backup:monitor`, and restore a copy of the resulting SQLite dump and media archive in a temporary location. A successful backup notification is not a substitute for validating the archive contents and restored database.

## Deployment

The application is hosted through Laravel Forge. A deployment should install locked dependencies, build production assets, run forward-only migrations, refresh optimized caches, and ensure the scheduler and queue worker are active.

Run `php artisan app:verify-production` after loading the production environment and before applying migrations. After deployment, run `php artisan app:verify-deployment EXPECTED_COMMIT_SHA`; it verifies the checked-out commit, pending migrations, scheduler and queue heartbeats, and backup freshness without printing sensitive values.

The production smoke workflow runs every six hours and on demand. Along with `npm run test:e2e:production`, it provides bounded, read-only checks for critical routes, the admin redirect, and response security headers.

Public contact and newsletter messages are queued on the configured Laravel queue. Production must run and monitor a long-lived queue worker for the `default` queue, restart it during deployments, and alert on failed jobs. A successful form response means the message was accepted for delivery, not that the mail provider has delivered it.

The media migration copies existing Media Library associations to native path columns before dropping the package table. Back up the database and `storage/app/public` before deploying that migration.

See [`docs/operations.md`](docs/operations.md) for the ordered deployment, backup-validation, and rollback runbook.
