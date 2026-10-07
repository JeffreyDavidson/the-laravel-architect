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
   and never reuse production backup/storage write credentials. Staging takes no
   scheduled backups (see "Production-only scheduled tasks").
8. Add one Forge database queue worker (`default` queue, timeout 60 seconds,
   tries 3) and a per-minute scheduler against the staging `current` directory.
   Confirm timeout stays below the queue's 90-second retry interval. Set
   `TLA_DEPLOYMENT_ENVIRONMENT=staging` before enabling the scheduler: backups,
   YouTube jobs and media checks run only when it is `production`, while the
   heartbeat and pruning tasks run on both sites. Preserve the working Nightwatch agent; do not add a
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
2. Confirm `DB_DATABASE` points to the persistent live SQLite database, not a release-local copy, and that production uses a busy timeout of at least 5000 milliseconds, WAL journal mode, and `NORMAL` or `FULL` synchronous writes. `config/database.php` also sets `IMMEDIATE` transactions: under `DEFERRED`, a read-then-write transaction such as the database queue reserving a job fails at once with "database is locked" when another process writes in between, the busy timeout never applies, and Laravel then fails that job permanently without using its retries. `app:verify-production` resolves symlinks and rejects missing files and files inside the release directory (or Forge's `releases` directory). Run verification after persistent storage is mounted.
3. Confirm `BACKUP_MEDIA_PATH` points to the persistent `storage/app/public` directory outside the release directory. Application archives contain only the SQLite database dump and this uploaded-media directory; source code is recovered from GitHub, and `.env` must remain excluded.
4. Create and independently validate a SQLite snapshot and public-media archive: run `php artisan backup:run`, then `php artisan app:verify-backup` immediately afterwards, and require it to exit successfully. See "Automated archive verification" below. The backup gate in the production deploy script (installed on Forge and confirmed working in release 2026.10.1, Forge deployment 79150690) does this automatically before it migrates a release with pending migrations; run it by hand for any other production change.
5. Keep both artifacts until the deployment and post-deployment checks are complete.

For a migration that changes media or database structure, do not proceed without a valid database snapshot and a valid media archive.

Keep a worker consuming the `database` queue connection. The contact email job is transactionally inserted there alongside the inquiry, even if the default queue connection changes. Leave `DB_QUEUE_CONNECTION` unset or set it to the application's default database connection; a separate queue database is rejected before an inquiry is saved. The existing Forge database worker consumes these jobs on the default queue without an additional queue or service. If an inquiry's admin page shows an email as not sent, use **Retry unsent emails** within 23 hours; after that, check the mail provider before contacting the sender manually.

With `APP_ENV=production`, every absolute URL, including newsletter confirmation links, is built from `APP_URL` instead of the request's Host header, so `APP_URL` must be each site's canonical HTTPS address (the staging address on staging).

Both sites sit behind Cloudflare. On the server, Forge's Nginx configuration uses the `real_ip` module with Cloudflare's published IP ranges to restore each visitor's address from Cloudflare's header (verified on production in October 2026), so PHP already sees the real client IP and the application trusts no proxies. A new or rebuilt server must get the same Nginx `real_ip` configuration before it takes traffic; without it every request appears to come from a Cloudflare edge, and the IP-keyed rate limits (contact form, newsletter, search, unsubscribe, Resend webhook) would throttle unrelated visitors together.

## Synchronizing public production content to staging

Run `php artisan content:sync-production --staging` from the staging release to replace staging's public content with the current production versions. The flag permits `APP_ENV=production` only on `staging.thelaravelarchitect.com`; local environments do not require it. The command transfers only published posts, projects and newsletter issues, referenced categories and tags, active podcasts and their published episodes, published videos, their SEO metadata, and referenced public media. Responsive image variants are regenerated after media transfer, including same-path replacements.

Synchronization and archive import share a target guard that always rejects production hostnames, regardless of `APP_ENV` or the staging flag. Synchronization maps posts to a non-login staging content owner and never exports private project repository URLs, production users, subscribers, authentication data, review notes, activity logs, failed jobs, cache or session data, credentials, or environment configuration. Content that is no longer public in production is unpublished in staging while staging-only drafts remain intact.

## Reviewing orphaned media

`php artisan media:find-orphans` only reports candidates. It runs weekly on production (see "Production-only scheduled tasks"). Its optional `--delete` mode is destructive and requires operator approval. It deletes only unreferenced files in managed media directories after a 24-hour grace period, reading record and embedded-content references again once, immediately before the delete phase, so a file that content started to use during the scan is kept (the reference queries run twice in total, however many orphans there are). Attachments referenced by Markdown and SEO images are retained. Unknown directories and recent uploads are retained for review; they are not automatically safe to delete. A nonzero result can therefore mean retained candidates or missing referenced files, not just a failed storage operation. Without `--delete`, which is how the scheduler runs it, the command exits nonzero whenever any orphaned file or missing referenced file exists, so the weekly run emails a failure for as long as one orphan remains, until it is reviewed and removed. This sits uneasily with the rule in the failed-job section below not to schedule a command that fails merely because retained records exist; the owner has not yet decided which behavior to keep. Take a recoverable backup before any approved cleanup.

## Moving podcast episodes to Transistor

TLA's shows are not on Transistor yet. When a show moves:

1. For each episode, copy its share link from Transistor (`https://share.transistor.fm/s/{id}`) into **Transistor episode URL** on the episode's edit page. The form rejects any other URL.
2. Use the episodes table's **Missing Transistor URL** filter as the checklist: it lists published episodes that still have no share link. The hidden **Transistor** column shows which episodes are done.
3. Check a few public episode pages: once a share link is set, the Transistor player replaces the uploaded audio player and any Spotify or Apple embed.
4. The old audio players are retired, and their `audio_url`, `audio_path` and `embed_url` columns are dropped. The migration is irreversible and refuses to run while any episode, trashed or not, still holds a value in one of those columns; if it stops, move that episode to Transistor or clear the value, then run it again. Take and verify a backup first (see "Backup validation").
5. Audio files uploaded before the retirement are no longer referenced by any record. Any left under `storage/app/public/episodes/audio/` appear as orphans in the media orphan report and can be reviewed and removed with it.

## Resend bounce and complaint webhook

Resend reports bounces and spam complaints to `POST https://thelaravelarchitect.com/webhooks/resend`; the application then suppresses those addresses so they are never mailed again (see `docs/architecture.md`). To set it up on production:

1. Sign in to the Resend account that owns the `thelaravelarchitect.com` domain, not another project's account, and confirm the account name at the top left of the dashboard before changing anything. Resend only shows a webhook to the account that owns it, so a webhook created in the wrong account never receives this site's events and its direct link returns a 404. Resend signs in with Google, so the Google account that is active in the browser decides which Resend account opens; choose it at the Google account chooser. In 1Password this login is the item named "Resend - The Laravel Architect": a Google sign-in for the Google account that owns this site's Resend account, which signs in with a passkey. It is separate from the Mouse28 and KneadIt Resend login, which owns a different set of domains.
2. Open Webhooks, add `https://thelaravelarchitect.com/webhooks/resend` and select `email.bounced`, `email.complained` and `email.suppressed`. Resend webhooks are account-wide, so events for other projects' mail on the same account also arrive; they are ignored unless the address is one of this site's subscribers.
3. Copy the signing secret (`whsec_...`) into the production environment as `RESEND_WEBHOOK_SECRET` in Forge, then **deploy** (or run `config:clear` and `config:cache`) and restart the queue workers. Production caches config, so a saved `.env` value is not used until then.
4. Send a test event from the Resend dashboard and confirm a 2xx in its delivery log. A 503 means the secret is not loaded; a 403 means the secret does not match this webhook, or Cloudflare blocked the request. The site is behind Cloudflare: if the delivery log shows a 403 or a challenge page instead of a 2xx, add a Cloudflare WAF skip rule for `/webhooks/resend`.
5. Verify the full path with a real bounce: subscribe `bounced@resend.dev` on the live site, then confirm the event succeeds in the delivery log and the subscriber has `suppressed_at` set with `suppression_reason` of `bounced`. Use `complained@resend.dev` the same way for the complaint path, expecting `complained`. Only events sent after the webhook exists are delivered; Resend does not replay earlier ones.

An empty-body 403 with a secret that looks correct almost always means the running app still holds the old secret (stale config cache): redeploy, then use **Replay** on the failed event, or let Resend's automatic retry succeed. To compare without revealing the secret, run `php artisan tinker --execute 'echo strlen((string) config("services.resend.webhook_secret"));'` in Forge and compare the length with the secret shown in Resend.

The endpoint skips the session and CSRF middleware because Resend posts server to server; the signature is the only credential, and it is limited to 60 requests per minute per IP. Event payloads and addresses are never logged. A malformed signature header gets the same 403 as a wrong one, and the Resend package's own `/resend/webhook` route is disabled in `config/resend.php`. `app:verify-deployment` does not require the secret, so a release can ship before the webhook exists. Staging never sends email and needs no webhook.

## Sending a newsletter issue

**Send test email** and **Send to subscribers** both save the edit form before sending, so unsaved edits are stored and the email matches what is on screen. If the form has invalid data, the validation errors show and nothing is sent.

1. Publish the issue, then use **Send test email** on its edit page. The copy goes to `MAIL_CONTACT_TO`, records no delivery, and omits unsubscribe headers.
2. Check the Resend dashboard's daily and monthly sending quota against the active-subscriber count shown in the send confirmation. A send that exceeds the quota fails part-way; remaining deliveries retry for up to a day.
3. Confirm the production database queue worker is running, then use **Send to subscribers**. The button appears only for published issues that have not been sent, and a send cannot be undone.
4. Watch the edit page subheading (`Delivered to N of M subscribers`). Deliveries are rate-limited to five per second. Subscribers who unsubscribe before their delivery runs are skipped and removed from the count.
5. Deliveries that still fail after three exceptions or a day of retries appear in Nightwatch as failed jobs. Retrying a failed job is safe: a delivery that was already sent is skipped.

Staging keeps `MAIL_MAILER=log` and never receives production subscribers, so sends there cannot reach readers.

## Deploying

The Forge deployment should install locked Composer dependencies, build assets, refresh optimized caches, run forward-only migrations as the last step that can fail, activate the release, and restart the queue worker. The scheduler must continue running every minute.

Production's database queue worker runs `queue:work 'database' --sleep=3 --daemon --quiet --timeout=60 --tries=3 --max-time=3600 --memory=128` (owner-applied in Forge and read back on 2026-10-06; it previously ran with `--timeout=90`). Keep the worker timeout several seconds below the queue's 90-second `retry_after`, or a job that runs that long can be reserved again before the first attempt is stopped. `--max-time` and `--memory` make the worker restart itself hourly and when it grows past 128 MB on the shared 1 GB server. Jobs that declare their own timeout (`DeliverNewsletterIssue`, `SendContactInquiryEmails`: 60 seconds) stay below `retry_after` too.

For production, run `php artisan app:verify-production` after loading the release environment and before applying migrations. Stop the deployment if the command reports an unsafe or incomplete setting. Do not force production mail or backup credentials into staging to satisfy this production-specific verifier.

### Forge deploy script

Staging and production are separate Forge sites, and each holds its own copy of this script. Keep the two copies identical: the site ID guard selects the per-site behavior. After changing the script, paste it into both sites.

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

export NODE_OPTIONS="--max-old-space-size=1024"
npm ci --production=false
npm run build

# Recreate the public storage link in the new release
rm -f public/storage
$FORGE_PHP artisan storage:link

if test "$FORGE_SITE_ID" = 3044519; then
    $FORGE_PHP artisan app:verify-production --no-ansi

    # Releases with pending migrations need a fresh, verified backup first. Fail closed.
    tla_pending="$($FORGE_PHP artisan migrate:status --pending --no-ansi)"
    if [[ "$tla_pending" != *"No pending migrations"* ]]; then
        tla_backup_verified=false
        for tla_attempt in 1 2; do
            $FORGE_PHP artisan backup:run --no-ansi
            if $FORGE_PHP artisan app:verify-backup --no-ansi; then
                tla_backup_verified=true
                break
            fi
        done
        test "$tla_backup_verified" = true
    fi
fi
$FORGE_PHP artisan optimize
# Migrate last: once the schema has changed, nothing may stop the release from activating.
$FORGE_PHP artisan migrate --force

# Records the deploy in Nightwatch; a failure here must not block activation.
$FORGE_PHP artisan nightwatch:deploy "$FORGE_DEPLOY_COMMIT" --ref="$FORGE_DEPLOY_COMMIT" \
    || echo "nightwatch:deploy failed; check the deployment marker in Nightwatch." >&2

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

The backup gate runs only on production, and only when the release has pending migrations, so ordinary deploys are not slowed. Staging is skipped because it has no B2 destination. It takes a new backup and verifies it straight away; a write between the two commands can make the verification fail, so it repeats the pair once before stopping the deployment. If both attempts fail, the script exits before `migrate --force` and the previous release keeps serving traffic. Look at the failure output, fix the cause, and redeploy. A failed `migrate:status` also stops the deploy, because the assignment runs under `set -e`.

`$ACTIVATE_RELEASE()` is required for Forge zero-downtime deployments. Without it, Forge can report that a deployment completed while `current` still points to the previous release. Keep activation after all preparation steps so a failed build or check leaves the previous release serving traffic.

`migrate --force` must stay the last step that can stop the deploy before activation. The migration changes the shared live database, so once it has run the new release has to go live: a failure after it would leave the previous release serving a schema it was not written for. The asset install and build and `storage:link` therefore run first, before the backup gate, and a failed `npm ci` or `npm run build` stops the deploy with the database untouched. `nightwatch:deploy` runs after the migration, so it only records a deploy whose migrations succeeded, and its `|| echo` keeps a Nightwatch failure from blocking activation (the package already exits successfully on API errors; this also covers a crash). Confirm the marker in the Nightwatch dashboard as described under "Nightwatch deployment tracking". `$RESTART_QUEUES()` must follow activation so long-running workers are restarted against the active release. See the [Forge deployment documentation](https://laravel.com/forge/docs/sites/deployments#release-creation-and-activation).

### Observability environments

Production uses Nightwatch for server-side telemetry and deployment tracking. Staging verification does not require a Nightwatch agent. Telescope is intended for staging inspection but is not installed or configured by this repository; do not assume a Telescope dashboard is available. Until that separate installation and access-control work is completed, use configured Sentry exception reporting and Forge/application logs for staging diagnosis. Use one Sentry project for The Laravel Architect and label events with `SENTRY_ENVIRONMENT=production` or `SENTRY_ENVIRONMENT=staging`. Set `TLA_DEPLOYMENT_ENVIRONMENT` to the same value. `/robots.txt` is generated from it: only `production` allows crawlers (keeping them out of `/admin` and the signed `/preview/` URLs, but not `/search`, whose results are `noindex` and must be crawlable for that tag to be read) and advertises the sitemap from `APP_URL`; every other value, including staging, returns `Disallow: /`. Do not add a static `public/robots.txt`, because the web server would serve it before Laravel. The route runs without the `web` middleware group, so it sets no session or CSRF cookies, and it is sent with `Cache-Control: public, max-age=3600` so it behaves like a static file at the CDN. On production, Cloudflare currently answers `/robots.txt` itself with its managed "content signals" comment block (no rules, no `Sitemap:` line) even though Bot Preference Sync is off and no rule or Worker matches the path; the same route arrives intact at `/index.php/robots.txt`. Making the response cookie-free and cacheable did not change this, so the cause is a Cloudflare-side setting (support ticket pending). After a deploy, check the application's file at `/index.php/robots.txt` and the public file at `/robots.txt`, and treat a public file with no `User-agent` line as this Cloudflare problem, not an application fault. Until it is resolved, submit `/sitemap.xml` in Search Console so sitemap discovery does not depend on robots.txt. Do not reuse Mouse28 tokens, DSNs, or projects.

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

Forge exposes the immutable release commit as `FORGE_DEPLOY_COMMIT`. Run Nightwatch's deployment command from the new release after its caches and assets are ready. The Forge script sends this deployment marker after migrating and just before activating the release, without letting a failure stop the deploy:

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

### Production uptime check

The `Production uptime` workflow runs every ten minutes and on manual dispatch. It uses only `curl` and `jq` (preinstalled on GitHub-hosted runners), with no checkout, installed dependencies or secrets. It requires `https://thelaravelarchitect.com/up` to return HTTP 200 within 20 seconds and `https://thelaravelarchitect.com/deployment.json` to contain a 40-character `revision`. A failed check is retried once after 30 seconds before the job fails. Logs show only status codes and timings.

GitHub emails the repository owner when a scheduled run fails, so keep Actions failure notifications enabled in GitHub notification settings. GitHub can delay or skip scheduled runs, and in practice runs them far less often than the cron asks (about three runs in 22 hours were observed after the workflow was added), so this check is not a guaranteed or timely alert. An external monitor such as UptimeRobot or Better Stack watching `/up` is the reliable option for independent alerting.

### Ownership of operational checks

| Responsibility | Owner |
| --- | --- |
| Release preparation, activation, queue supervision, scheduler cron | Forge |
| Public application readiness after deployment | Forge deployment health check calling `/up` |
| Application configuration and telemetry privacy safeguards | `app:verify-production` before migrations |
| Active checkout, migrations, runtime heartbeats, and production Nightwatch | `app:verify-deployment` after activation |
| Backup freshness and destination health | Scheduled Spatie `backup:monitor` with failure email (production only) |
| Stored responsive-media integrity | Scheduled `media:verify-responsive-images` with failure email (production only) |
| Unreferenced or missing media files | Scheduled weekly `media:find-orphans` with failure email (production only); cleanup stays manual |
| YouTube video data | Scheduled `youtube:stats` (daily) and `youtube:sync` (weekly), production only, no failure email |
| Ongoing public-route and HTTP health coverage | Production and staging smoke workflows |
| Frequent production availability check | `Production uptime` workflow, with GitHub failure emails to the repository owner |

The production site's Forge deployment health check was enabled on 2026-09-19 with `https://thelaravelarchitect.com/up` as its URL. Keep it enabled and require HTTP 200 from this endpoint. Reconfirm the setting in Forge when changing deployment configuration. Forge owns the external request, while the application owns its database and heartbeat checks. A running Supervisor process is not proof that queue jobs are executing, so retain the queued heartbeat. Allow heartbeat initialization after clearing cache before expecting readiness. See [Forge deployment health checks](https://laravel.com/forge/docs/sites/deployments#deployment-health-checks).

Backup monitoring uses the same configured disks as backup creation, with a maximum age of one day and a 5,000 MB storage limit in `config/backup.php`. It runs daily at 04:00 on production and emails failures. This is periodic detection, not continuous monitoring. The obsolete `BACKUP_MAX_AGE_HOURS` setting is no longer read; remove it during an approved environment maintenance change if present. Validated pre-migration backups and restore drills remain required independently of the release verifier.

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
- The `Deploy staging` workflow passes for the selected revision before production promotion. The separate scheduled `Staging smoke` workflow checks availability every twelve hours using main-branch test definitions and Cloudflare Access credentials; it is not release approval evidence. The smoke client retries a request once, after two seconds, only when the connection fails; an HTTP error status is never retried.

For content or authorization changes, also verify the affected public route and authenticated admin boundary.

### Production-only scheduled tasks

Staging and production share one small Forge server (1 GB, 1 vCPU), which overloaded around 2026-09-26 and on 2026-10-03/04. Staging holds only a copy of production's public content, so its backups protect nothing and its YouTube runs spend API quota. These tasks in `routes/console.php` therefore run only on production:

- `backup:run` and `app:verify-backup` (daily at `BACKUP_RUN_AT`, default 02:00 UTC, verification straight after the backup), `backup:clean` and `backup:monitor`
- `youtube:stats` (daily at 00:00 UTC) and `youtube:sync` (weekly, Sunday at 00:00 UTC)
- `media:verify-responsive-images` (daily at 05:00 UTC) and `media:find-orphans` (weekly, Sunday at 05:30 UTC); both email the scheduler's failure output to the backup notification recipient when they exit nonzero. `media:find-orphans` runs without `--delete`, so it fails whenever an orphan or missing referenced file exists (see "Reviewing orphaned media")

Every daily and weekly task uses `withoutOverlapping()` with an explicit lock expiry (120 minutes for the backup and its verification, 60 for the rest) instead of the 24-hour default. A run killed mid-way, as when the server ran out of memory on 2026-10-03, leaves its lock behind; with the default it could still be held when the next day's run is due and silently skip it.

Staging runs with `APP_ENV=production`, so the gate is `TLA_DEPLOYMENT_ENVIRONMENT` (`app.deployment_environment`), not `APP_ENV` or the scheduler's `environments()` filter. The tasks run only when it is `production`. The setting falls back to `APP_ENV` when unset, so staging must set `TLA_DEPLOYMENT_ENVIRONMENT=staging` or these jobs run there too; confirm this when reviewing the staging environment. The scheduler heartbeat, `queue:prune-failed`, `model:prune`, `activitylog:clean` and `cache:prune-expired` still run on both sites; `cache:prune-expired` (daily at 03:30) deletes expired `cache` table rows, such as per-IP rate limiter entries, which the database cache store otherwise never removes.

Owner note: archives that staging's scheduled backups wrote before this change can be deleted. With staging's `BACKUP_DISKS` unset or `local`, they sit on the `local` disk under `storage/app/private/<APP_NAME>/` in the staging site's storage; check the path before deleting anything.

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

Run it straight after `backup:run`: a write between the two commands shows up as a row-count or media mismatch, so rerun both. On production, the scheduler runs it every day straight after the scheduled backup: both are due at `BACKUP_RUN_AT` (default `02:00`), and one scheduler run executes due tasks one at a time in the order `routes/console.php` defines them, so verification starts the moment the backup finishes. Keep `app:verify-backup` defined after `backup:run`, and never run the backup in the background, or the two would race. A failure is emailed to the backup notification address like the other scheduled checks; if the backup itself failed, verification checks the previous archive and usually fails too. A verified backup therefore normally exists at deploy time, and the production deploy script also takes and verifies a fresh one before it applies any pending migration (see "Forge deploy script"), so no manual step is needed for a release with migrations. A non-zero exit means the backup must not be relied on for a release. The manual drill below remains the fallback check; an actual restore follows "Restore from backup". The old `BACKUP_VERIFY_AT` setting is no longer read; remove it from the production environment during an approved maintenance change if present.

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
5. Rebuild a database from the extracted SQL dump (`db-dumps/sqlite-*.sql`) with `sqlite3 -bail restored.sqlite < dump.sql`, then run `PRAGMA quick_check` on it; require exactly `ok`.
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

## Restore from backup

Restoring replaces the live database and media with an older copy and loses every change made since that backup. It runs only with the owner's explicit approval for the specific archive, and only the owner runs it on the server: agents never run any part of it. Practise it on staging, never first on production. Run the commands as the `forge` user from the site's `current` directory (`/home/forge/thelaravelarchitect.com/current` on production), and never paste the archive password anywhere.

1. Choose the archive with the owner and record its name (`php artisan backup:list` shows them; on B2 they sit under `<APP_NAME>/`). Run `php artisan app:verify-backup` first if it is the newest one.
2. Note the live database path (`php artisan tinker --execute 'echo config("database.connections.sqlite.database");'`) and the media path (`BACKUP_MEDIA_PATH`).
3. Put the site in maintenance mode with `php artisan down`. In Forge, stop the production queue worker and pause the production scheduler, so nothing writes while the files change. Maintenance mode also holds back the scheduler and worker, but stopping them in Forge keeps them from holding the old database open.
4. Download and decrypt the archive into a private temporary directory. The archive is an AES-encrypted ZIP; the password is read from the application's configuration, so it never reaches the shell:

   ```bash
   umask 077
   export TLA_RESTORE_DIR="$(mktemp -d)"
   export TLA_ARCHIVE="<APP_NAME>/<archive>.zip"
   php artisan tinker --execute 'file_put_contents(getenv("TLA_RESTORE_DIR")."/backup.zip", Storage::disk("b2-backups")->readStream(getenv("TLA_ARCHIVE")));'
   php artisan tinker --execute '$zip = new ZipArchive; $zip->open(getenv("TLA_RESTORE_DIR")."/backup.zip"); $zip->setPassword(config("backup.backup.password")); echo $zip->extractTo(getenv("TLA_RESTORE_DIR")."/extracted") ? "extracted" : "FAILED";'
   ```

   Stop unless it prints `extracted`.
5. The archive holds a plain SQL dump (made with `sqlite3 .dump`), not a copy of the database file. Rebuild a new database file from it and check it:

   ```bash
   sqlite3 -bail "$TLA_RESTORE_DIR/restored.sqlite" < "$TLA_RESTORE_DIR"/extracted/db-dumps/sqlite-*.sql
   sqlite3 "$TLA_RESTORE_DIR/restored.sqlite" 'PRAGMA quick_check;'
   ```

   Require exactly `ok`. The application switches the file to WAL mode when it next connects.
6. Stop PHP access to the database: stop PHP-FPM (`sudo service php8.5-fpm stop`, using the server's PHP version). Staging shares this PHP-FPM, so it is down for the same few minutes.
7. Swap the file in. Move the live database aside together with its `-wal` and `-shm` files, never deleting them, so no stale WAL file is applied to the restored database, then copy the restored file into place and match the old file's owner and permissions (`ls -l`):

   ```bash
   TLA_DB="<live database path>"
   TLA_STAMP="$(date -u +%Y%m%d%H%M%S)"
   for tla_suffix in "" -wal -shm; do
       if test -e "$TLA_DB$tla_suffix"; then mv "$TLA_DB$tla_suffix" "$TLA_DB$tla_suffix.before-restore-$TLA_STAMP"; fi
   done
   cp "$TLA_RESTORE_DIR/restored.sqlite" "$TLA_DB"
   ```

8. Restore public media the same way: move the live `BACKUP_MEDIA_PATH` directory aside with the same suffix, then copy the extracted copy into place. The archive stores media under its full path, so it is at `$TLA_RESTORE_DIR/extracted` followed by `BACKUP_MEDIA_PATH`.
9. Start PHP-FPM again (`sudo service php8.5-fpm start`), then clear the caches, including the database cache table restored with the dump, and rebuild them: `php artisan optimize:clear` then `php artisan optimize`.
10. While still in maintenance mode, run `php artisan migrate:status`. If the archive predates a migration in the active release, a migration shows as pending: stop and decide with the owner whether to redeploy the release that matches the archive (see "Rollback") or migrate. Check the restored `jobs` table as well: jobs queued at backup time may already have run since (a contact email, a newsletter delivery) and would run again.
11. Restart the queue worker and resume the scheduler in Forge, then leave maintenance mode with `php artisan up`. The scheduler and worker do not run while the site is down, so the runtime checks below only pass after this step.
12. Verify: `/up` returns HTTP 200, `/deployment.json` still shows the active revision, `php artisan app:verify-deployment <that revision>` passes, and `/`, `/blog`, a post, a public media URL and the `/admin` sign-in load. Run `php artisan media:verify-responsive-images`.
13. Record the archive, the time of the restore and the checks in the incident notes, without credentials or private content. Keep the `.before-restore-*` copies until the owner agrees they can go, then remove only the temporary restore directory.

## Rollback

1. Stop the release if post-deployment verification fails.
2. Do not restore over the live database or media directory until the exact targets are confirmed.
3. With the owner's explicit approval, restore the validated SQLite snapshot and media archive by following "Restore from backup".
4. Redeploy the last known-good commit.
5. Re-run migration, route, media, queue, and scheduler verification.
6. Record the failure, restoration commands, artifact paths, and final production commit.

Never delete the only validated rollback artifacts during an incident.

## Forge API migration

The application repository contains no direct Forge API client or `/api/v1` request. Application deployment must not depend on undocumented Forge API v1 requests.
