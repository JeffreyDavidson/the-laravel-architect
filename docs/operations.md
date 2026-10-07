# Operations

How The Laravel Architect is deployed, watched, backed up and recovered. It
covers isolated staging and explicitly approved production deployments on
Laravel Forge. See [the release process](releases.md) for branch policy and
promotion gates. A merge into `main` does not deploy production.

Read the page for the task at hand before any production work.

## Deploying

- [Environments](operations/environments.md): the Forge sites, GitHub
  environments and secrets, Cloudflare and Nginx setup, application settings,
  and what staging needs.
- [Deploying](operations/deploying.md): checks before a deploy, the queue
  worker, and verification after a deploy.
- [Forge deploy script](operations/forge-deploy-script.md): the pinned script
  both sites run, its backup gate, and why the steps are in that order.

## Running the site

- [Observability](operations/observability.md): Sentry, Nightwatch, the Forge
  deployment health check and the `Production uptime` workflow.
- [Scheduled tasks](operations/scheduled-tasks.md): production-only tasks, tasks
  on both sites, and failed-job retention.
- [Media](operations/media.md): storage setup, responsive image repair and
  verification, generated post artwork, and orphaned media review.

## Backups and recovery

- [Backups](operations/backups.md): the B2 destination, monitoring, automated
  and manual verification, and the restore drill record.
- [Restore and rollback](operations/restore-and-rollback.md): restoring an
  archive over the live site, and rolling back a failed release.

## Runbooks

- [Sending a newsletter issue](operations/runbooks/newsletter-send.md)
- [Resend bounce and complaint webhook](operations/runbooks/resend-webhook.md)
- [Synchronizing public production content to staging](operations/runbooks/staging-content-sync.md)
- [Moving podcast episodes to Transistor](operations/runbooks/transistor-move.md)

## History

- [Staged-release cutover, September 2026](history/2026-09-staged-release-cutover.md)

## Ownership of operational checks

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
