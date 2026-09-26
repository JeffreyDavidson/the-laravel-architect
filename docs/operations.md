# Deployment operations

This runbook covers isolated staging and explicitly approved production
deployments on Laravel Forge. See [the release process](releases.md) for branch
policy and promotion gates. A merge into `main` does not deploy production.

## Staged-release setup and cutover

Targets in organization `jeffrey-davidson`, server `cold-moon` (753072): staging
site 3366565 and production site 3044519. Direct push-to-deploy was disabled for
both sites on 2026-09-19; production's `/up` health check remains enabled. GitHub
environments were created with main-only branch policies; production requires
Jeffrey's review and permits self-review. Both environments disallow administrator
bypass. The staged-release workflow is present on `main` and runs only when
`STAGED_RELEASES_ENABLED=true`. Treat the dated setup records and checklist below
as cutover history and prerequisites, not proof of current deployment status.
Both sites have the shared pinned Forge deployment script saved, and
the uncached deployment-marker Nginx location is installed on both sites.

Complete these steps before setting the GitHub repository variable
`STAGED_RELEASES_ENABLED=true`:

1. Create GitHub environments `staging` and `production`, allowing deployments
   from `main` only. Require Jeffrey's approval for `production` and disallow
   administrator bypass. Self-approval must remain possible for a single-person
   operator who dispatches and approves their own deployment.
2. Store each site's own existing Forge hook as its environment's
   `FORGE_DEPLOY_HOOK` secret. Never use a production hook in staging or a broad
   account API token when the site hook is sufficient. Supplying these secrets
   grants CI deployment authority and requires explicit operator approval.
3. Configure a Cloudflare Access service token authorized only for this staging
   application. Store `CF_ACCESS_CLIENT_ID` and `CF_ACCESS_CLIENT_SECRET` in both
   GitHub environments: production promotion needs read access to staging to
   revalidate the approved candidate. Do not make staging public. Credential
   creation and access-policy changes require separate approval; never paste
   tokens into chat, logs, repository files or workflow inputs.
4. Configure both sites for branch `main`, with Forge push-to-deploy **off**.
   CI triggers staging only after tests pass; production uses the manual
   promotion workflow. Do not manually redeploy staging while it is being
   reviewed or promoted. A one-off operational deployment must use the same
   revision pinning and approval requirements.
5. Install the shared Forge script below for each site. Production script or
   Nginx changes require immediate confirmation of the target. Do not trigger a
   production deployment during setup. Do not enable the workflows while either
   site still uses an unpinned script.
6. Add an exact Nginx location for the revision marker within each site's server
   block, preserving the existing configuration and validating it before reload:

   ```nginx
   location = /deployment.json {
       try_files $uri =404;
       add_header Cache-Control "no-store" always;
   }
   ```

   Bypass Cloudflare caching for `/deployment.json` and `/up`. The marker exposes
   only a source revision and Forge deployment ID, never environment values.
7. Finish staging runtime setup: isolated persistent SQLite and storage,
   database queue/cache tables, `QUEUE_CONNECTION=database`,
   `CACHE_STORE=database`, and `BACKUP_MEDIA_PATH` pointing to staging's own
   persistent `storage/app/public`. Keep `MAIL_MAILER=log` or an approved sandbox,
   and never reuse production backup/storage write credentials. Staging local
   backups are not a substitute for production's B2 backup policy.
8. Add one Forge database queue worker (`default` queue, timeout 60 seconds,
   tries 3) and a per-minute scheduler against the staging `current` directory.
   Confirm timeout stays below the queue's 90-second retry interval. Review all
   scheduled tasks before enabling them: backups, pruning and YouTube sync are
   not all production-only. Preserve the working Nightwatch agent; do not add a
   duplicate. Refresh staging configuration, observe both fresh heartbeats,
   then enable `RUNTIME_HEALTH_ENABLED=true` and refresh configuration again.
9. After the migration PR reaches `main`, enable the repository variable and
   rerun successful push CI for that commit to exercise automatic staging.
   Verify Access, the noncached revision marker, runtime checks and HTTP smoke
   suite before using production promotion. Record the successful staging run.

On 2026-09-19, the staging queue/cache/media-path environment entries were
activated and cached configuration was refreshed. Forge now reports one running
database queue worker and an installed per-minute scheduler; runtime health is
enabled. Both Forge deployment scripts are installed and pinned, and the
staging and production Nginx configurations contain the exact uncached
`/deployment.json` location. Do not enable the staged-release workflow until a
pinned staging deployment has been verified.

## Before deploying

1. Confirm the target commit and review its migration and storage changes.
2. Confirm `DB_DATABASE` points to the persistent live SQLite database, not a release-local copy, and that production uses a busy timeout of at least 5000 milliseconds, WAL journal mode, and `NORMAL` or `FULL` synchronous writes. `app:verify-production` resolves symlinks and rejects missing files and files inside the release directory (or Forge's `releases` directory). Run verification after persistent storage is mounted.
3. Confirm `BACKUP_MEDIA_PATH` points to the persistent `storage/app/public` directory outside the release directory. Application archives contain only the SQLite database dump and this uploaded-media directory; source code is recovered from GitHub, and `.env` must remain excluded.
4. Create and independently validate a SQLite snapshot and public-media archive: run `php artisan backup:run`, then `php artisan app:verify-backup` immediately afterwards, and require it to exit successfully. See "Automated archive verification" below.
5. Keep both artifacts until the deployment and post-deployment checks are complete.

For a migration that changes media or database structure, do not proceed without a valid database snapshot and a valid media archive.

Keep a worker consuming the `database` queue connection. Contact notifications are transactionally inserted there alongside the inquiry, even if the default queue connection changes. Leave `DB_QUEUE_CONNECTION` unset or set it to the application's default database connection; a separate queue database is rejected before an inquiry is saved. The existing Forge database worker consumes these jobs without an additional queue or service.

## Synchronizing public production content to staging

Run `php artisan content:sync-production --staging` from the staging release to replace staging's public content with the current production versions. The flag permits `APP_ENV=production` only on `staging.thelaravelarchitect.com`; local environments do not require it. The command transfers only published posts, projects and newsletter issues, referenced categories and tags, active podcasts and their published episodes, published videos, their SEO metadata, and referenced public media. Responsive image variants are regenerated after media transfer, including same-path replacements.

Synchronization and archive import share a target guard that always rejects production hostnames, regardless of `APP_ENV` or the staging flag. Synchronization maps posts to a non-login staging content owner and never exports private project repository URLs, production users, subscribers, authentication data, review notes, activity logs, failed jobs, cache or session data, credentials, or environment configuration. Content that is no longer public in production is unpublished in staging while staging-only drafts remain intact.

## Reviewing orphaned media

`php artisan media:find-orphans` only reports candidates. Its optional `--delete` mode is destructive and requires operator approval. It deletes only unreferenced files in managed media directories after a 24-hour grace period, rechecking record and embedded-content references before deletion. Attachments referenced by Markdown and SEO images are retained. Unknown directories and recent uploads are retained for review; they are not automatically safe to delete. A nonzero result can therefore mean retained candidates or missing referenced files, not just a failed storage operation. Take a recoverable backup before any approved cleanup.

## Sending a newsletter issue

1. Publish the issue, then use **Send test email** on its edit page. The copy goes to `MAIL_CONTACT_TO`, records no delivery, and omits unsubscribe headers.
2. Check the Resend dashboard's daily and monthly sending quota against the active-subscriber count shown in the send confirmation. A send that exceeds the quota fails part-way; remaining deliveries retry for up to a day.
3. Confirm the production database queue worker is running, then use **Send to subscribers**. The button appears only for published issues that have not been sent, and a send cannot be undone.
4. Watch the edit page subheading (`Delivered to N of M subscribers`). Deliveries are rate-limited to five per second. Subscribers who unsubscribe before their delivery runs are skipped and removed from the count.
5. Deliveries that still fail after three exceptions or a day of retries appear in Nightwatch as failed jobs. Retrying a failed job is safe: a delivery that was already sent is skipped.

Staging keeps `MAIL_MAILER=log` and never receives production subscribers, so sends there cannot reach readers.

## Deploying

The Forge deployment should install locked Composer dependencies, build assets, run forward-only migrations, refresh optimized caches, and restart the queue worker. The scheduler must continue running every minute.

For production, run `php artisan app:verify-production` after loading the release environment and before applying migrations. Stop the deployment if the command reports an unsafe or incomplete setting. Do not force production mail or backup credentials into staging to satisfy this production-specific verifier.

### Shared staging and production Forge deploy script

Install this script only after the revision-marker setup above is complete.
Forge's `forge_deploy_commit` parameter is metadata, not checkout pinning. The
separate `revision` and `source_branch` hook parameters become
`FORGE_VAR_REVISION` and `FORGE_VAR_SOURCE_BRANCH`; validate both before
executing application code. Staging accepts `main` or a numbered
`release/YYYY.MM.N` source branch. Production accepts only `main`. Require the
source branch to still point at the exact tested revision before checkout.
Direct Deploy-button requests without both parameters intentionally fail
closed.

```bash
set -e

test "$FORGE_SITE_BRANCH" = main
case "$FORGE_SITE_ID" in
    3366565)
        test "$FORGE_SITE_ROOT" = /home/forge/staging.thelaravelarchitect.com
        [[ "${FORGE_VAR_SOURCE_BRANCH:-}" = main || "${FORGE_VAR_SOURCE_BRANCH:-}" =~ ^release/[0-9]{4}\.(0[1-9]|1[0-2])\.[0-9]+$ ]]
        ;;
    3044519)
        test "$FORGE_SITE_ROOT" = /home/forge/thelaravelarchitect.com
        test "${FORGE_VAR_SOURCE_BRANCH:-}" = main
        ;;
    *) exit 1 ;;
esac
[[ "${FORGE_VAR_REVISION:-}" =~ ^[a-f0-9]{40}$ ]]
test "$FORGE_DEPLOY_COMMIT" = "$FORGE_VAR_REVISION"

$CREATE_RELEASE()
cd $FORGE_RELEASE_DIRECTORY

if test "$(git rev-parse --is-shallow-repository)" = true; then
    git fetch --unshallow origin
fi
git fetch --no-tags origin "$FORGE_VAR_SOURCE_BRANCH:refs/remotes/origin/$FORGE_VAR_SOURCE_BRANCH"
test "$(git rev-parse "origin/$FORGE_VAR_SOURCE_BRANCH")" = "$FORGE_VAR_REVISION"
git checkout --detach "$FORGE_VAR_REVISION"
test "$(git rev-parse HEAD)" = "$FORGE_VAR_REVISION"
test ! -e public/deployment.json

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if test "$FORGE_SITE_ID" = 3044519; then
    $FORGE_PHP artisan app:verify-production --no-ansi
fi
$FORGE_PHP artisan optimize
$FORGE_PHP artisan migrate --force

export NODE_OPTIONS="--max-old-space-size=1024"
npm ci --production=false
npm run build

$FORGE_PHP artisan nightwatch:deploy "$FORGE_DEPLOY_COMMIT" --ref="$FORGE_DEPLOY_COMMIT"

# Recreate the public storage link in the new release
rm -f public/storage
$FORGE_PHP artisan storage:link

$ACTIVATE_RELEASE()
$RESTART_QUEUES()

# Allow the real scheduler and worker to populate heartbeat checks after cache changes.
tla_verified=false
for tla_attempt in $(seq 1 24); do
    if $FORGE_PHP artisan app:verify-deployment "$FORGE_VAR_REVISION" --no-ansi; then
        tla_verified=true
        break
    fi
    sleep 5
done
test "$tla_verified" = true
test "$(readlink -f "$FORGE_SITE_PATH")" = "$(pwd -P)"
[[ "$FORGE_DEPLOYMENT_ID" =~ ^[1-9][0-9]*$ ]]
# This is the completion signal. Publish only after activation and verification.
printf '{"revision":"%s","deployment_id":"%s"}\n' \
    "$FORGE_VAR_REVISION" "$FORGE_DEPLOYMENT_ID" > public/deployment.json.tmp
mv public/deployment.json.tmp public/deployment.json
```

`$ACTIVATE_RELEASE()` is required for Forge zero-downtime deployments. Without it, Forge can report that a deployment completed while `current` still points to the previous release. Keep activation after all preparation steps so a failed build or check leaves the previous release serving traffic. `$RESTART_QUEUES()` must follow activation so long-running workers are restarted against the active release. See the [Forge deployment documentation](https://laravel.com/forge/docs/sites/deployments#release-creation-and-activation).

### Observability environments

Production uses Nightwatch for server-side telemetry and deployment tracking. Staging verification does not require a Nightwatch agent. Telescope is intended for staging inspection but is not installed or configured by this repository; do not assume a Telescope dashboard is available. Until that separate installation and access-control work is completed, use configured Sentry exception reporting and Forge/application logs for staging diagnosis. Use one Sentry project for The Laravel Architect and label events with `SENTRY_ENVIRONMENT=production` or `SENTRY_ENVIRONMENT=staging`. Set `TLA_DEPLOYMENT_ENVIRONMENT` to the same value. Do not reuse Mouse28 tokens, DSNs, or projects.

Sentry is limited to exception reporting until a separate performance and privacy review approves broader collection:

```dotenv
SENTRY_LARAVEL_DSN=<project DSN>
SENTRY_ENVIRONMENT=<production-or-staging>
SENTRY_RELEASE=<immutable deployment commit>
SENTRY_TRACES_SAMPLE_RATE=0.0
SENTRY_PROFILES_SAMPLE_RATE=0.0
SENTRY_SEND_DEFAULT_PII=false
```

`SENTRY_RELEASE` falls back to Forge's `FORGE_DEPLOY_COMMIT`, but an explicit value may be used when verifying an environment outside a Forge deployment. Never print the DSN in deployment logs or diagnostics.

The application fixes `sentry.max_request_body_size` to `never`, and production verification enforces it. Container-resolved event and breadcrumb callbacks also filter request data, query strings, sensitive keyed values, email addresses, and token-bearing URL paths. They preserve exception classes and stack locations for diagnosis. These targeted filters do not make arbitrary free-form application logs safe to populate with personal data or credentials.

### Nightwatch

Nightwatch is required only in production. In the Nightwatch dashboard, create the production application environment, then use Forge's built-in Nightwatch integration from the production site's Overview tab. Supply the production token through Forge, enable monitoring, and set these values:

```dotenv
NIGHTWATCH_ENABLED=true
NIGHTWATCH_TOKEN=<environment-specific token>
NIGHTWATCH_REQUEST_SAMPLE_RATE=0.1
NIGHTWATCH_COMMAND_SAMPLE_RATE=1.0
NIGHTWATCH_EXCEPTION_SAMPLE_RATE=1.0
NIGHTWATCH_SCHEDULED_TASK_SAMPLE_RATE=1.0
NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE=false
NIGHTWATCH_CAPTURE_REQUEST_PAYLOAD=false
NIGHTWATCH_IGNORE_MAIL=true
```

Never store the token in the repository. Leave the ingest URI, timeouts, event buffer, and server identifier at the package defaults unless the Forge integration requires an explicit override. The production verifier requires a nonempty server identifier, positive ingest timeouts, and a positive event buffer. The Forge integration manages the required application-specific agent process. Do not add a second manual process for the same site. If the built-in integration is unavailable, add one Forge background process named `Nightwatch` that runs `php artisan nightwatch:agent` from the site directory with one process and a 15-second graceful shutdown.

After enabling or changing Nightwatch, refresh the application's cached configuration and run `php artisan nightwatch:status`. Require a successful status before considering monitoring operational. Keep request sampling at 10% initially; production verification requires a positive rate no higher than 10%, and a lower rate may be used after reviewing event volume. Command, exception, and scheduled-task sampling must remain positive and no higher than 100%. Request payload, request-header, exception source-code, mail-event, and application-log capture must remain disabled unless a separate privacy review approves them. The production verifier also prevents required payload and header redactions from being removed. Contact-mail subjects and free-form log messages or context can contain personal or secret values. The configured `nightwatch` log channel intentionally uses a null handler, even if it is accidentally added to `LOG_STACK`. Request URLs and execution previews replace complete paths and IP addresses with Laravel route templates so newsletter tokens and signed URL signatures are not ingested by request or child-event telemetry. Unmatched routes use a fixed placeholder. Outgoing-request telemetry retains only each URL's scheme and host; user information, ports, paths, query strings, and fragments are removed. Command telemetry retains only the registered command name so arguments and options are not ingested. Cache keys and authenticated user IDs are replaced with application-keyed HMAC digests, preserving correlation without exposing source values. Query telemetry preserves SQL structure while replacing raw string and numeric literals and comments. Exception telemetry preserves class, code, file, line, and a type-only stack trace while replacing all free-form messages and unhandled-exception previews. Do not restore Nightwatch's default user details or raw identifiers without the same privacy review.

### Nightwatch deployment tracking

Forge exposes the immutable release commit as `FORGE_DEPLOY_COMMIT`. Run Nightwatch's deployment command from the new release after its caches and assets are ready. The Forge script sends this deployment marker before activating the release:

```bash
php artisan nightwatch:deploy "$FORGE_DEPLOY_COMMIT" --ref="$FORGE_DEPLOY_COMMIT"
```

The application configuration falls back to the same Forge value for `nightwatch.deployment`. Run `php artisan app:verify-deployment "$FORGE_DEPLOY_COMMIT"` afterward; it fails if the application is reporting a different Nightwatch deployment identifier. The package's deployment command reports API failures in its output but currently exits successfully, so confirm that the matching deployment marker appears in the Nightwatch dashboard rather than relying on its exit code alone.

### Nightwatch dashboard baseline

After production telemetry is visible, configure the dashboard to notify the operational recipient for any unhandled exception, failed queued job, and failed scheduled task. Establish slow-request and slow-query thresholds from observed production baselines instead of arbitrary local timings. Review sampled request volume after the first full traffic cycle and lower the request rate when the retained data is sufficient; do not raise it above 10% without a cost and privacy review.

After the first deployment and after material Nightwatch configuration changes, confirm that:

- the expected deployment marker and server identifier are visible;
- sampled requests use route templates and contain no IP address, headers, payload, or route parameter values;
- exceptions, SQL, cache keys, user identifiers, commands, and outgoing URLs retain only their documented redacted forms;
- queued jobs, scheduled tasks, and notifications are arriving without message bodies or recipient details;
- mail and application-log events are absent;
- each configured alert reaches the monitored operational destination.

After enabling runtime monitoring or clearing the application cache, run `php artisan schedule:run` and allow the queue worker to process the heartbeat probe before relying on `/up`.

When a release introduces responsive uploaded images, run `php artisan media:repair-responsive-images` once after the persistent public-media directory is mounted. The command repairs projects, posts, and podcasts in one bounded, isolated run while preserving original uploads, creating WebP derivatives beside them, skipping derivatives that already pass verification, and returning a failure if any source file is missing, unsupported, or still unhealthy after the aggregate verification pass. Concurrent repair or resource-specific generation runs are rejected so they cannot race over the same derivatives. Use `--force` only when a release intentionally requires every valid derivative to be re-encoded. The resource-specific generation commands remain available for targeted recovery. Use `php artisan media:verify-responsive-images` separately for read-only checks; it reports aggregate results without exposing stored paths and does not modify media. Do not remove the original images.

Production also runs `media:verify-responsive-images` daily at 05:00 and emails its aggregate output only when verification fails. Treat that notification as media-integrity degradation and arrange an approved repair or restore. Existing media damage is not automatically a release failure; releases that change media behavior still require targeted media verification.

Failed derivative generation during an admin upload leaves the original upload and any previously valid derivatives available, and writes a path-free warning identifying the appropriate retry command. Do not add stored media paths to that log context.

Do not run a standalone production migration unless the deployment itself cannot apply the migration and the release plan explicitly authorizes it.

## After deploying

Run the deployment verifier from the active site's `current` directory with the immutable commit expected for the release, not from an inactive release directory:

```bash
php artisan app:verify-deployment EXPECTED_COMMIT_SHA
```

The command fails when the checked-out commit differs, migrations are pending, queue or scheduler heartbeats are stale, or (in production) the Nightwatch deployment identifier differs or the Nightwatch agent is unavailable. Staging does not require Nightwatch; Telescope availability is not checked or implied. It does not scan backup storage or existing media. A matching CLI checkout alone does not prove HTTP traffic is serving that release: also verify activation and the public smoke checks below.

### Ownership of operational checks

| Responsibility | Owner |
| --- | --- |
| Release preparation, activation, queue supervision, scheduler cron | Forge |
| Public application readiness after deployment | Forge deployment health check calling `/up` |
| Application configuration and telemetry privacy safeguards | `app:verify-production` before migrations |
| Active checkout, migrations, runtime heartbeats, and production Nightwatch | `app:verify-deployment` after activation |
| Backup freshness and destination health | Scheduled Spatie `backup:monitor` with failure email |
| Stored responsive-media integrity | Scheduled `media:verify-responsive-images` with failure email |
| Ongoing public-route and HTTP health coverage | Production and staging smoke workflows |

The production site's Forge deployment health check was enabled on 2026-09-19 with `https://thelaravelarchitect.com/up` as its URL. Keep it enabled and require HTTP 200 from this endpoint. Reconfirm the setting in Forge when changing deployment configuration. Forge owns the external request, while the application owns its database and heartbeat checks. A running Supervisor process is not proof that queue jobs are executing, so retain the queued heartbeat. Allow heartbeat initialization after clearing cache before expecting readiness. See [Forge deployment health checks](https://laravel.com/forge/docs/sites/deployments#deployment-health-checks).

Backup monitoring uses the same configured disks as backup creation, with a maximum age of one day and a 5,000 MB storage limit in `config/backup.php`. It runs daily at 04:00 and emails failures. This is periodic detection, not continuous monitoring. The obsolete `BACKUP_MAX_AGE_HOURS` setting is no longer read; remove it during an approved environment maintenance change if present. Validated pre-migration backups and restore drills remain required independently of the release verifier.

Then verify all of the following against the deployed commit:

- Production `HEAD` matches the expected commit.
- `php artisan migrate:status` has no pending migrations.
- The migration was recorded exactly once with a positive batch.
- The expected schema is present and obsolete schema is absent.
- `/` and `/admin` return the expected status codes.
- `/up` returns HTTP 200, confirming the application can read its migrated database and both the scheduler and queue worker have fresh heartbeats.
- Public media URLs return successful responses.
- The queue worker and scheduler are active.
- Production `php artisan nightwatch:status` confirms the Nightwatch agent is accepting connections.
- The production Nightwatch dashboard contains the deployment marker matching the expected commit.
- A reversible upload smoke test can create, read, and delete a temporary object.
- The manually dispatched `Production smoke` GitHub Actions workflow passes. It is also run every six hours.
- The `Deploy staging` workflow passes for the selected revision before production promotion. The separate scheduled `Staging smoke` workflow checks availability every twelve hours using main-branch test definitions and Cloudflare Access credentials; it is not release approval evidence.

For content or authorization changes, also verify the affected public route and authenticated admin boundary.

## Backup validation

### Off-server destinations

Production uses `b2-backups` as its sole scheduled backup destination, confirmed and retained by operator decision on 2026-09-19. Backup creation and monitoring must cover that same destination. Local and NAS copies are not required by the current policy; adding another destination requires a separate operational decision. This leaves B2 as the only off-server backup destination, so preserve archive encryption, failure notifications, and regular restore validation.

Backblaze B2 provides an encrypted off-server copy through its S3-compatible API. Configure these values through the production secret manager, never in the repository:

```dotenv
BACKUP_DISKS=b2-backups
BACKUP_B2_KEY_ID=<bucket-scoped key ID>
BACKUP_B2_APPLICATION_KEY=<bucket-scoped application key>
BACKUP_B2_REGION=us-east-005
BACKUP_B2_BUCKET=jdavidson-tla-production-backups
BACKUP_B2_ENDPOINT=https://s3.us-east-005.backblazeb2.com
BACKUP_ARCHIVE_PASSWORD=<independent archive password>
```

Restrict the B2 application key to the TLA backup bucket with read and write access. Keep the bucket private, retain client-side archive encryption, and do not reuse the Mouse28 key or bucket. The configured endpoint must use HTTPS on Backblaze's `backblazeb2.com` domain.

After an approved change to these values, refresh the configuration and prove the B2 destination works:

```bash
php artisan config:clear
php artisan app:verify-production
php artisan backup:run
php artisan backup:monitor
php artisan app:verify-backup
```

Confirm a new encrypted archive exists on the `b2-backups` disk and that `app:verify-backup` passes against the copy it downloads from B2. A successful connection does not prove that an application backup can be restored.

### Automated archive verification

`php artisan app:verify-backup` performs the independent checks below against the newest archive on every configured destination. It downloads the archive into a new `0700` directory under the system temp directory and requires every file entry to be encrypted, decrypt, and read in full at its recorded size. Every path must be a database dump or sit under `BACKUP_MEDIA_PATH`. The command restores the dump with the same `sqlite3` CLI that creates it, runs `PRAGMA quick_check` on the restored and live databases, compares the migration list and every persistent table's row count, and compares the media file count and five sampled SHA-256 hashes with the live media directory. `cache`, `cache_locks`, `sessions`, `jobs`, and `job_batches` are excluded as transient. The temporary directory is always removed, and the output contains only counts, table names, and pass or fail reasons, never the archive password or backed-up content.

Run it straight after `backup:run`: a write between the two commands shows up as a row-count or media mismatch, so rerun both. A non-zero exit means the backup must not be relied on for a release. The manual drill below remains the fallback and the procedure for an actual restore.

An exit-zero backup command is not enough. Independently verify:

- SQLite `PRAGMA quick_check` returns `ok` for the live database and snapshot.
- Source and snapshot migration counts match.
- Source and snapshot record counts match for affected tables.
- PHP's `ZipArchive` can decrypt and read every archive entry.
- The archive contains only files from the intended media root.
- Archived file count matches the source file count.

Retain the artifacts until the release is independently verified. Copy them to an approved off-server destination as soon as the security and retention policy allows.

### Encrypted restore drill

Perform this drill in an isolated temporary directory, never over the live database or media directory:

1. Set a restrictive umask and create a unique directory with `mktemp -d`.
2. Copy one explicit backup archive into that directory. Confirm its resolved source path before copying.
3. Supply `BACKUP_ARCHIVE_PASSWORD` through the process environment or approved secret manager. Never paste it into a command, log, ticket, or shell history.
4. Use PHP's `ZipArchive` to set the password, test every encrypted entry, and extract the archive into a child directory. Stop if any entry cannot be decrypted or read.
5. Locate the extracted SQLite database and run `PRAGMA quick_check`; require exactly `ok`.
6. Compare the restored and live migration lists and the record counts for critical tables.
7. Confirm restored media paths remain inside the isolated extraction root, then compare file counts and sample file hashes.
8. Record the archive timestamp, checks performed, and result without recording credentials or private content.
9. After review, verify the temporary path again and remove only that isolated restore directory.

Latest completed drill (2026-09-23): archive `2026-09-23-02-00-11.zip`
(02:00:17 UTC). All 25 ZIP entries decrypted and read. The live and restored
SQLite databases passed `PRAGMA quick_check`; all 36 migrations and row counts
for 20 persistent tables matched. `cache_locks` and `sessions` counts changed
between the backup and live database, as expected for transient tables. All 24
public-media paths and the total file count matched, and five sampled SHA-256
hashes matched. The isolated temporary copy was removed.

Run `php artisan app:test-backup-notification` after configuring or changing the production mail transport. The command sends an identifiable test message to `BACKUP_NOTIFICATION_EMAIL` and does not create a backup.

Nightwatch reports new failed jobs. Failures are retained for `QUEUE_FAILED_JOB_RETENTION_HOURS` and then pruned by Laravel's native `queue:prune-failed` command; do not add a scheduled command that fails merely because retained records exist, because Laravel will surface every nonzero scheduled run as a new exception.

## Rollback

1. Stop the release if post-deployment verification fails.
2. Do not restore over the live database or media directory until the exact targets are confirmed.
3. Restore the validated SQLite snapshot and media archive using the approved Forge procedure.
4. Redeploy the last known-good commit.
5. Re-run migration, route, media, queue, and scheduler verification.
6. Record the failure, restoration commands, artifact paths, and final production commit.

Never delete the only validated rollback artifacts during an incident.

## Forge API migration

The application repository contains no direct Forge API client or `/api/v1` request. Application deployment must not depend on undocumented Forge API v1 requests.
