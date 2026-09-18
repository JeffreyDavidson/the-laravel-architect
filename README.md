# The Laravel Architect

The personal website, blog, portfolio, podcast archive, and newsletter for Jeffrey Davidson. The application uses Laravel 13, Blade, Tailwind CSS 4, and a Filament 5 administration panel.

## Requirements

- PHP 8.5 or newer with SQLite and GD support
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

The email passed to the Filament command must match `ADMIN_EMAIL`. The database seeder manages only the administrator account; it does not recreate editorial content. Restore content from a validated database backup or import a content:export-public archive when preparing another environment.

## Quality checks

```bash
composer test
composer test:types
composer test:filament
composer lint:check
npm run build
npm run test:assets
composer audit --locked
npm audit --omit=dev
```

See [`tests/TESTING.md`](tests/TESTING.md) for the boundary between Unit, Integration, Feature, Browser, Architecture, and Playwright e2e tests.

CI validates Composer configuration and runs dependency auditing, formatting, static analysis, asset compilation and budget checks for the public and admin bundles, Pest Browser checks, and the Pest suite on pull requests targeting `develop` or `main` and on pushes to `main`. Both protected branches require the `Laravel` check, so the already-verified pull request is not run a second time after it is squash-merged into `develop`. Superseded runs are cancelled.

The separate Dependency audit workflow checks locked PHP dependencies and production JavaScript dependencies every Monday at 08:43 UTC and supports manual runs. This catches newly published advisories between releases without changing dependencies.

### Rector review

Run `composer test:rector` to preview application PHP and Laravel upgrades without modifying files. `rector.php` derives the PHP target (currently 8.5) and Laravel rules from Composer. It scans application code, bootstrap configuration, configuration files, factories, seeders, and routes; existing migrations and generated files are outside its configured paths.

Run `composer test:rector:pest` to review tests separately using `rector-pest.php`. This configuration includes PHP and Laravel upgrades plus `PestSetList::CODING_STYLE`, including expectation chaining.

A nonzero result can mean changes are suggested, not that the application is broken. Review the proposed diffs before applying any refactoring. This check is advisory and is not part of required CI.

### Test static analysis

Both PHPStan configurations use maximum level with `treatPhpDocTypesAsCertain: false` and separate caches under `storage/framework/cache`. The test command sets `APP_ENV=testing`. `tests/pest-livewire.stub` preserves component-specific typing for the Pest Livewire helper. `tests/pest-browser.stub` describes the browser helper's conditional return and the SDK's fluent `wait()` wrapper. These analysis-only stubs should be revisited when upstream supplies equivalent typing.

Run `composer test:types:pest` to analyze `tests/` at PHPStan's maximum level using `phpstan-pest.neon`. Larastan and the Pest extension are automatically registered by Composer's extension installer. Both this check and the independent application check, `composer test:types`, are required in CI. Neither configuration suppresses errors or adds a baseline.

## Architecture

Public pages are server-rendered Blade views. Controllers pass simple page data directly, while complex multi-source or pagination-aware payloads such as the homepage, blog index, category and tag archives, and post, podcast, and project detail pages are assembled by ViewModels. Pages provide page-specific `SEOData` or an SEO-enabled content model to the shared layout, which renders titles, descriptions, canonical links, social metadata, and robots directives. Reusable content selection, including related posts, related projects, and adjacent-episode navigation, lives in query objects rather than controllers. Sitemap and RSS serialization, the newsletter subscription lifecycle, and contact message delivery live in focused actions, leaving their HTTP controllers responsible for request and response concerns. The shared JSON-LD graph uses named Laravel routes for canonical site, author, static-page, article, podcast, episode, project case-study, collection, item-list, and breadcrumb entities. Paginated public archives reject out-of-range pages and use page-specific titles, descriptions, canonical and collection URLs, and continuous item positions. Route-model binding uses content slugs, while publication scopes keep drafts and future content off public pages, feeds, and the sitemap. Dynamic sitemap archives report the latest modification date from their public content. Signed newsletter confirmation routes compare the presented token against a non-mass-assignable SHA-256 hash in middleware, and newsletter action pages are explicitly excluded from indexing.

Public interaction modules live in `resources/js`, shared presentation rules live in `resources/css`, and both are compiled through Vite. Compressed budgets guard the public entry points and lazy modules in CI. The Filament theme is a separate budgeted Vite entry loaded only by the admin panel. Editorial images that participate in the build live in `resources/images` and are referenced with `Vite::asset()`. Files that must retain a stable direct URL for browsers or third-party consumers, such as favicons and Filament branding, remain in `public`. The asset-budget check rejects unexpected files in `public/images`, so new images must either use the Vite pipeline or be intentionally added to the direct-URL allowlist.

The blog archive returns 12 articles per page, ordered by publication date and ID. Its GET search and category filters work without JavaScript and persist in pagination links. Search covers article titles, excerpts, and localized tag names; `%` and `_` are literal search characters. Search result pages are excluded from indexing, and invalid filters or out-of-range pages return 404.

Scheduled posts and episodes become public when their publication date arrives, without a scheduler job or a stored status change. Drafts, posts in review, undated content, and future content remain private; episodes also require an active podcast. Projects require Published status. Admin forms generate an initial slug but preserve it when titles change, and explicit slug edits retain uniqueness validation.

Public project pages present summaries, optional screenshots, authored write-ups, and contact links. Project repository URLs remain available in admin records but are not rendered as public links or included in project JSON-LD. Editors should also avoid inserting private repository URLs into public descriptions, write-ups, or website-link fields. The general author GitHub profile link is independent of project repository visibility.

The projects index separates featured and additional projects using the shared `projects.index-entry` Blade component, styled entirely with Tailwind utilities. Entries show existing screenshots with available responsive variants, or use a text-only layout without fabricated fallback imagery. An empty-state message and the persistent contact section keep the page useful when no projects are published.

Generated post OG images are cached on the private local filesystem. Cache validity is based on the rendered title, category name, and an explicit renderer version; deleting a post removes its cached image.

The Filament panel is available at `/admin`. Panel admission requires the native `is_admin` flag, resource actions are protected by Laravel policies, and app-based multi-factor authentication is required in production.

Uploaded images and audio are validated and stored through Laravel's `public` filesystem disk. Models store explicit file paths and remove replaced or record-owned files; deleting a podcast also removes media owned by its database-cascaded episodes. Project and post featured images and podcast cover images retain their original upload as the canonical fallback and generate 640px and 1280px WebP variants for responsive public rendering. Bundled podcast cover fallbacks provide 128px, 320px, and 512px Vite-managed variants for smaller episode artwork. Replacing or deleting an uploaded image also removes its variants. Run `php artisan storage:link` on a new environment, then use `php artisan media:repair-responsive-images` when backfilling or repairing existing uploads.

Media removal waits for a successful database commit, including responsive variants, post OG caches, and media from cascaded episode deletions. Public episode playback prefers uploaded audio over an external audio URL, while retaining both stored inputs for editors. HTTPS Spotify embed URLs and Apple Podcasts embed URLs can render in the public player; unsupported embed hosts are omitted.

Newsletter subscriptions use a signed, expiring double-opt-in link followed by an explicit confirmation form, preventing link scanners from changing subscriber state. Subscriber-specific signed unsubscribe links use the same explicit form pattern and should be included in every newsletter. Contact and newsletter submissions include abuse controls. Content changes are recorded with Spatie Activity Log.

Newsletter confirmation emails have a 15-minute cooldown per normalized email address, coordinated through hashed cache keys and an atomic lock. Repeated requests during that window preserve the existing confirmation link, including requests from different IP addresses. An enqueue failure leaves retries available; the public response does not disclose subscription status.

Application responses set a constrained Content Security Policy plus cross-origin isolation, clickjacking, transport-security, MIME-sniffing, referrer, and browser-feature policy headers globally. Public scripts use a per-request nonce instead of `unsafe-inline` or `unsafe-eval`; the Filament admin path retains those allowances for framework compatibility.

The `/up` health endpoint verifies both the Laravel runtime and access to the migrated application database. Production monitoring should treat any non-200 response as unhealthy.

## Scheduled work

The production scheduler must run every minute. It dispatches:

- a runtime heartbeat every minute to verify both the scheduler and queue worker
- application and database backups daily
- backup cleanup weekly
- backup health monitoring daily
- failed-job pruning daily, with failure alerting provided by Nightwatch
- `youtube:stats` daily
- `youtube:sync` weekly

YouTube tasks prevent overlapping execution. The homepage displays the latest published videos from the local sync without requesting external statistics during page rendering.

Production must set `DB_DATABASE` to the absolute path of the live SQLite database. The SQLite connection uses Laravel's native busy timeout, WAL journal mode, and `NORMAL` synchronous writes to tolerate normal web, scheduler, and queue concurrency. Set `BACKUP_MEDIA_PATH` to the absolute path of the persistent public-media directory outside the release directory. Application backups intentionally contain only the SQLite database dump and persistent uploaded media; GitHub remains the recovery source for application code, and `.env` is explicitly excluded from archives. Set `BACKUP_DISKS=local,nas-backups,b2-backups`, configure the `BACKUP_SFTP_*` values and independently verified NAS host fingerprint, configure the bucket-scoped `BACKUP_B2_*` credentials and HTTPS endpoint, and set `BACKUP_ARCHIVE_PASSWORD` before enabling off-server backups. `MAIL_CONTACT_TO` and `BACKUP_NOTIFICATION_EMAIL` must point to monitored mailboxes.

Create a Cloudflare Turnstile widget for the production host and set `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `TURNSTILE_CONTACT_ACTION=contact-form`, and `TURNSTILE_ALLOWED_HOSTNAMES=thelaravelarchitect.com,www.thelaravelarchitect.com`. Contact submissions fail closed when verification is unavailable or the verified action and hostname do not match the configured values.

Set `RUNTIME_HEALTH_ENABLED=true` in production. The scheduler records its heartbeat and dispatches a queued probe every minute; `/up` returns an unhealthy response when either heartbeat is older than `RUNTIME_HEALTH_MAX_AGE` seconds.

Production and staging observability use isolated Laravel Nightwatch environments plus a shared Sentry project labeled with the matching deployment environment. Both integrations are disabled by default for local development and tests. Nightwatch samples at most 10% of web requests and applies the application's strict telemetry redaction layer. Sentry is limited to exception reporting: tracing and profiling remain disabled, default personally identifiable information and SQL bindings are not collected, and each release is identified by the immutable Forge commit. Production verification enforces these privacy controls and rejects missing or mismatched environment labels. Keep all Nightwatch tokens and the Sentry DSN in Forge, never in the repository. See [`docs/operations.md`](docs/operations.md) for setup, alert baselines, deployment tracking, and verification.

Sentry request-body capture is disabled independently of its default PII setting. Event and breadcrumb callbacks filter request data, query strings, sensitive keyed values, email addresses, and token-bearing URL paths while retaining exception stack locations.

Set `QUEUE_FAILED_JOB_RETENTION_HOURS` to the retention window. Laravel's native scheduled pruning removes expired failure records, while Nightwatch reports new failed jobs without turning retained failures into repeated scheduler exceptions.

After changing backup configuration, run `php artisan backup:run`, `php artisan backup:monitor`, and restore a copy of the resulting SQLite dump and media archive in a temporary location. A successful backup notification is not a substitute for validating the archive contents and restored database.

## Deployment

The application is hosted through Laravel Forge. A deployment should install locked dependencies, build production assets, run forward-only migrations, refresh optimized caches, and ensure the scheduler and queue worker are active.

Run `php artisan app:verify-production` after loading the production environment and before applying migrations. After deployment, run `php artisan app:verify-deployment EXPECTED_COMMIT_SHA`; it verifies the checked-out commit, pending migrations, Nightwatch agent, scheduler and queue heartbeats, and backup freshness without printing sensitive values.

The production smoke workflow runs every six hours and on demand. The staging smoke workflow runs every twelve hours and on demand against the deployed `develop` baseline. Both workflows run the grouped Pest production smoke test with `PRODUCTION_BASE_URL` to provide bounded, read-only checks for critical routes, the admin redirect, and response security headers. The repository owner should keep GitHub Actions failure notifications enabled so scheduled smoke failures reach a monitored inbox.

Public contact and newsletter messages are queued on the configured Laravel queue. Production must run and monitor a long-lived queue worker for the `default` queue, restart it during deployments, and alert on failed jobs. A successful form response means the message was accepted for delivery, not that the mail provider has delivered it.

The media migration copies existing Media Library associations to native path columns before dropping the package table. Back up the database and `storage/app/public` before deploying that migration.

See [`docs/releases.md`](docs/releases.md) for versioning and release promotion, and [`docs/operations.md`](docs/operations.md) for the ordered deployment, backup-validation, and rollback runbook.
