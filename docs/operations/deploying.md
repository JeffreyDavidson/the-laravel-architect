# Deploying

Releases reach staging and production through the workflows in
[the release process](../releases.md). Each site runs the
[Forge deploy script](forge-deploy-script.md). This page covers the checks
around a deploy. A merge into `main` does not deploy production.

## Before deploying

1. Confirm the target commit and review its migration and storage changes.
2. Confirm `DB_DATABASE` points to the persistent live SQLite database, not a
   release-local copy. Production must use a busy timeout of at least 5000
   milliseconds, WAL journal mode, and `NORMAL` or `FULL` synchronous writes.
   - `config/database.php` also sets `IMMEDIATE` transactions. Under
     `DEFERRED`, a read-then-write transaction such as the database queue
     reserving a job fails at once with "database is locked" when another
     process writes in between. The busy timeout never applies, and Laravel then
     fails that job permanently without using its retries.
   - `app:verify-production` resolves symlinks and rejects missing files and
     files inside the release directory (or Forge's `releases` directory). Run
     verification after persistent storage is mounted.
3. Confirm `BACKUP_MEDIA_PATH` points to the persistent `storage/app/public`
   directory outside the release directory. Application archives contain only
   the SQLite database dump and this uploaded-media directory; source code is
   recovered from GitHub, and `.env` must remain excluded.
4. Create and independently validate a SQLite snapshot and public-media
   archive: run `php artisan backup:run`, then `php artisan app:verify-backup`
   immediately afterwards, and require it to exit successfully. See
   [Automated archive verification](backups.md#automated-archive-verification).
   The backup gate in the production deploy script (installed on Forge and
   confirmed working in release 2026.10.1, Forge deployment 79150690) does this
   automatically before it migrates a release with pending migrations; run it by
   hand for any other production change.
5. Keep both artifacts until the deployment and post-deployment checks are
   complete.

For a migration that changes media or database structure, do not proceed
without a valid database snapshot and a valid media archive.

## The deploy

The Forge deployment installs locked Composer dependencies, builds assets,
refreshes optimized caches, runs forward-only migrations as the last step that
can fail, activates the release, and restarts the queue worker. The scheduler
must continue running every minute. The [deploy script page](forge-deploy-script.md)
explains the order.

For production, run `php artisan app:verify-production` after loading the
release environment and before applying migrations (the deploy script does).
Stop the deployment if the command reports an unsafe or incomplete setting. Do
not force production mail or backup credentials into staging to satisfy this
production-specific verifier.

Do not run a standalone production migration unless the deployment itself
cannot apply the migration and the release plan explicitly authorizes it.

Some releases need a one-time command after deploying, such as the responsive
image repair described in [Media](media.md#responsive-image-repair).

## Queue worker

Production's database queue worker runs:

```bash
queue:work 'database' --sleep=3 --daemon --quiet --timeout=60 --tries=3 --max-time=3600 --memory=128
```

The owner applied this in Forge and read it back on 2026-10-06; it previously
ran with `--timeout=90`.

- Keep the worker timeout several seconds below the queue's 90-second
  `retry_after`, or a job that runs that long can be reserved again before the
  first attempt is stopped.
- `--max-time` and `--memory` make the worker restart itself hourly and when it
  grows past 128 MB on the shared 1 GB server.
- Jobs that declare their own timeout (`DeliverNewsletterIssue`,
  `SendContactInquiryEmails`: 60 seconds) stay below `retry_after` too.

Keep a worker consuming the `database` queue connection. The contact email job
is transactionally inserted there alongside the inquiry, even if the default
queue connection changes (see [Contact](../architecture/contact.md)).

- Leave `DB_QUEUE_CONNECTION` unset or set it to the application's default
  database connection; a separate queue database is rejected before an inquiry
  is saved.
- The existing Forge database worker consumes these jobs on the default queue
  without an additional queue or service.
- If an inquiry's admin page shows an email as not sent, use **Retry unsent
  emails** within 23 hours. After that, check the mail provider before
  contacting the sender manually.

### Removing the contact job forwarder

Release 2026.10.28 moved the contact-email job into creator-kit and left
`App\Jobs\SendContactInquiryEmails` as a forwarder for jobs queued or failed
before the move. Once `php artisan queue:failed` on production lists no
`App\Jobs\SendContactInquiryEmails` and no deploy before that release is still
draining, delete the class and its forwarder test in a normal PR.

## After deploying

Run the deployment verifier from the active site's `current` directory with the
immutable commit expected for the release, not from an inactive release
directory:

```bash
php artisan app:verify-deployment EXPECTED_COMMIT_SHA
```

The command fails when the checked-out commit differs, migrations are pending,
queue or scheduler heartbeats are stale, or (in production) the Nightwatch
deployment identifier differs or the Nightwatch agent is unavailable. Staging
does not require Nightwatch; Telescope availability is not checked or implied.
It does not scan backup storage or existing media. A matching CLI checkout alone
does not prove HTTP traffic is serving that release: also verify activation and
the public smoke checks below.

After enabling runtime monitoring or clearing the application cache, run
`php artisan schedule:run` and allow the queue worker to process the heartbeat
probe before relying on `/up`.

Then verify all of the following against the deployed commit:

- Production `HEAD` matches the expected commit.
- `php artisan migrate:status` has no pending migrations.
- The migration was recorded exactly once with a positive batch.
- The expected schema is present and obsolete schema is absent.
- `/` and `/admin` return the expected status codes.
- `/up` returns HTTP 200, confirming the application can read its migrated
  database and both the scheduler and queue worker have fresh heartbeats.
- Public media URLs return successful responses.
- The queue worker and scheduler are active.
- Production `php artisan nightwatch:status` confirms the Nightwatch agent is
  accepting connections.
- The production Nightwatch dashboard contains the deployment marker matching
  the expected commit.
- A reversible upload smoke test can create, read, and delete a temporary
  object.
- The manually dispatched `Production smoke` GitHub Actions workflow passes. It
  is also run every six hours.
- The `Deploy staging` workflow passes for the selected revision before
  production promotion. The separate scheduled `Staging smoke` workflow checks
  availability every twelve hours using main-branch test definitions and
  Cloudflare Access credentials; it is not release approval evidence. The smoke
  client retries a request once, after two seconds, only when the connection
  fails; an HTTP error status is never retried.
- `/robots.txt` and `/index.php/robots.txt` look right (see
  [robots.txt on production](environments.md#robotstxt-on-production)).

For content or authorization changes, also verify the affected public route and
authenticated admin boundary.

If verification fails, follow [Rollback](restore-and-rollback.md#rollback).
