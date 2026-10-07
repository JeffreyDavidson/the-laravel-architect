# Scheduled tasks

The scheduler runs every minute on both sites. Its tasks are defined in
`routes/console.php`.

## Production-only scheduled tasks

Staging and production share one small Forge server (1 GB, 1 vCPU), which
overloaded around 2026-09-26 and on 2026-10-03/04. Staging holds only a copy of
production's public content, so its backups protect nothing and its YouTube runs
spend API quota. These tasks therefore run only on production:

- `backup:run` and `app:verify-backup` (daily at `BACKUP_RUN_AT`, default
  02:00 UTC, verification straight after the backup), `backup:clean` and
  `backup:monitor`. See [Backups](backups.md).
- `youtube:stats` (daily at 00:00 UTC) and `youtube:sync` (weekly, Sunday at
  00:00 UTC).
- `media:verify-responsive-images` (daily at 05:00 UTC) and `media:find-orphans`
  (weekly, Sunday at 05:30 UTC). Both email the scheduler's failure output to
  the backup notification recipient when they exit nonzero. `media:find-orphans`
  runs without `--delete`, so it fails whenever an orphan or missing referenced
  file exists (see [Media](media.md#reviewing-orphaned-media)).

Every daily and weekly task uses `withoutOverlapping()` with an explicit lock
expiry (120 minutes for the backup and its verification, 60 for the rest)
instead of the 24-hour default. A run killed mid-way, as when the server ran out
of memory on 2026-10-03, leaves its lock behind; with the default it could still
be held when the next day's run is due and silently skip it.

### How the gate works

Staging runs with `APP_ENV=production`, so the gate is
`TLA_DEPLOYMENT_ENVIRONMENT` (`app.deployment_environment`), not `APP_ENV` or
the scheduler's `environments()` filter. The tasks run only when it is
`production`. The setting falls back to `APP_ENV` when unset, so staging must
set `TLA_DEPLOYMENT_ENVIRONMENT=staging` or these jobs run there too; confirm
this when reviewing the staging environment.

### Leftover staging archives

Archives that staging's scheduled backups wrote before backups became
production-only (October 2026) can be deleted by the owner. With staging's
`BACKUP_DISKS` unset or `local`, they sit on the `local` disk under
`storage/app/private/<APP_NAME>/` in the staging site's storage; check the path
before deleting anything.

## Tasks on both sites

The scheduler heartbeat, `queue:prune-failed`, `model:prune`,
`activitylog:clean` and `cache:prune-expired` still run on both sites.

- `cache:prune-expired` (daily at 03:30) deletes expired `cache` table rows,
  such as per-IP rate limiter entries, which the database cache store otherwise
  never removes.
- `model:prune` removes unconfirmed and unsubscribed newsletter subscribers (see
  [Newsletter](../architecture/newsletter.md#subscriber-lifecycle)).
- `activitylog:clean` removes activity log entries older than 365 days.

## Failed-job retention

Nightwatch reports new failed jobs. Failures are retained for
`QUEUE_FAILED_JOB_RETENTION_HOURS` and then pruned by Laravel's native
`queue:prune-failed` command. Do not add a scheduled command that fails merely
because retained records exist, because Laravel will surface every nonzero
scheduled run as a new exception.

`media:find-orphans` currently sits uneasily with this rule; see
[Media](media.md#reviewing-orphaned-media).
