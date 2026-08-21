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
composer audit --locked
npm audit --omit=dev
```

CI runs dependency auditing, formatting, static analysis, asset compilation, Playwright browser checks, and the Pest suite on every pull request and push to `develop` or `main`. Superseded runs are cancelled, and failed browser checks retain screenshots and traces for seven days. Workflow actions are pinned to immutable commits, with weekly Dependabot updates targeting `develop`.

## Architecture

Public pages are server-rendered Blade views. Route-model binding uses content slugs, while publication scopes keep drafts and future content off public pages, feeds, and the sitemap.

Generated post OG images are cached on the private local filesystem. Cache validity is based on the rendered title, category name, and an explicit renderer version; deleting a post removes its cached image.

The Filament panel is available at `/admin`. Panel admission requires the native `is_admin` flag, resource actions are protected by Laravel policies, and app-based multi-factor authentication is required in production.

Uploaded images and audio are validated and stored through Laravel's `public` filesystem disk. Models store explicit file paths and remove replaced or record-owned files; deleting a podcast also removes media owned by its database-cascaded episodes. Run `php artisan storage:link` on a new environment.

Newsletter subscriptions use a signed, expiring double-opt-in link followed by an explicit confirmation form, preventing link scanners from changing subscriber state. Subscriber-specific signed unsubscribe links use the same explicit form pattern and should be included in every newsletter. Contact, newsletter, and testimonial submissions include abuse controls. Content changes are recorded with Spatie Activity Log.

Application responses set clickjacking, MIME-sniffing, referrer, and browser-feature policy headers globally. Content Security Policy is intentionally deferred until Filament and Vite assets can use a tested nonce-based policy.

## Scheduled work

The production scheduler must run every minute. It dispatches:

- application and database backups daily
- backup cleanup weekly
- backup health monitoring daily
- `youtube:stats` daily
- `youtube:sync` weekly

YouTube tasks prevent overlapping execution. The homepage caches the subscriber count, retains the last successful value when YouTube is unavailable or returns malformed data, and displays the latest published videos from the local sync instead of date-sensitive promotional placeholders.

Production must set `DB_DATABASE` to the absolute path of the live SQLite database and `BACKUP_MEDIA_PATH` to the absolute path of the persistent public-media directory. Set `BACKUP_DISKS` to a comma-separated list that includes an off-server disk, such as `local,s3`, configure that disk's credentials, and set `BACKUP_ARCHIVE_PASSWORD` before enabling off-server backups. `MAIL_CONTACT_TO` and `BACKUP_NOTIFICATION_EMAIL` must point to monitored mailboxes.

After changing backup configuration, run `php artisan backup:run`, `php artisan backup:monitor`, and restore a copy of the resulting SQLite dump and media archive in a temporary location. A successful backup notification is not a substitute for validating the archive contents and restored database.

## Deployment

The application is hosted through Laravel Forge. A deployment should install locked dependencies, build production assets, run forward-only migrations, refresh optimized caches, and ensure the scheduler and queue worker are active.

Public contact and newsletter messages are queued on the configured Laravel queue. Production must run and monitor a long-lived queue worker for the `default` queue, restart it during deployments, and alert on failed jobs. A successful form response means the message was accepted for delivery, not that the mail provider has delivered it.

The media migration copies existing Media Library associations to native path columns before dropping the package table. Back up the database and `storage/app/public` before deploying that migration.

See [`docs/operations.md`](docs/operations.md) for the ordered deployment, backup-validation, and rollback runbook.
