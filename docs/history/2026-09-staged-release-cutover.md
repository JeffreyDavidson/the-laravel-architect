# Staged-release cutover, September 2026

This is a record of the move to staged releases. It is history, not a checklist
to follow and not proof of current deployment status. The cutover is complete:
the GitHub repository variable `STAGED_RELEASES_ENABLED` was set to `true` on
2026-09-19, and `Deploy staging` and `Promote production` have run releases
since. The settings this cutover put in place, and that still apply, are
described in [Environments](../operations/environments.md).

The text below is the setup record and checklist as they stood in the
operations runbook.

## Setup record

Targets in organization `jeffrey-davidson`, server `cold-moon` (753072): staging
site 3366565 and production site 3044519. Direct push-to-deploy was disabled for
both sites on 2026-09-19; production's `/up` health check remains enabled. GitHub
environments were created with main-only branch policies; production requires
Jeffrey's review and permits self-review. Both environments disallow administrator
bypass. The staged-release workflow is present on `main` and runs only when
`STAGED_RELEASES_ENABLED=true`. Both sites have the shared pinned Forge
deployment script saved, and the uncached deployment-marker Nginx location is
installed on both sites.

## Checklist

These steps were completed before setting the GitHub repository variable
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
5. Install the shared [Forge deploy script](../operations/forge-deploy-script.md)
   for each site. Production script or Nginx changes require immediate
   confirmation of the target. Do not trigger a production deployment during
   setup. Do not enable the workflows while either site still uses an unpinned
   script.
6. Add an exact Nginx location for the revision marker within each site's server
   block, preserving the existing configuration and validating it before reload
   (the block is in [Environments](../operations/environments.md#revision-marker)).
   Bypass Cloudflare caching for `/deployment.json` and `/up`.
7. Finish staging runtime setup: isolated persistent SQLite and storage,
   database queue/cache tables, `QUEUE_CONNECTION=database`,
   `CACHE_STORE=database`, and `BACKUP_MEDIA_PATH` pointing to staging's own
   persistent `storage/app/public`. Keep `MAIL_MAILER=log` or an approved sandbox,
   and never reuse production backup/storage write credentials. Staging takes no
   scheduled backups (see [Scheduled tasks](../operations/scheduled-tasks.md)).
8. Add one Forge database queue worker (`default` queue, timeout 60 seconds,
   tries 3) and a per-minute scheduler against the staging `current` directory.
   Confirm timeout stays below the queue's 90-second retry interval. Set
   `TLA_DEPLOYMENT_ENVIRONMENT=staging` before enabling the scheduler: backups,
   YouTube jobs and media checks run only when it is `production`, while the
   heartbeat and pruning tasks run on both sites. Preserve the working Nightwatch
   agent; do not add a duplicate. Refresh staging configuration, observe both
   fresh heartbeats, then enable `RUNTIME_HEALTH_ENABLED=true` and refresh
   configuration again.
9. After the migration PR reaches `main`, enable the repository variable and
   rerun successful push CI for that commit to exercise automatic staging.
   Verify Access, the noncached revision marker, runtime checks and HTTP smoke
   suite before using production promotion. Record the successful staging run.

## Status on 2026-09-19

The staging queue/cache/media-path environment entries were activated and
cached configuration was refreshed. Forge reported one running database queue
worker and an installed per-minute scheduler; runtime health was enabled. Both
Forge deployment scripts were installed and pinned, and the staging and
production Nginx configurations contained the exact uncached `/deployment.json`
location. The runbook then said not to enable the staged-release workflow until
a pinned staging deployment had been verified; the variable was enabled later
that day.
