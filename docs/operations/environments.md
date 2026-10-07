# Environments

Staging and production are separate Laravel Forge sites. This page records how
they are set up and what a rebuilt server or site needs. The dated setup record
is in [the staged-release cutover history](../history/2026-09-staged-release-cutover.md).

## Sites

Both sites live in Forge organization `jeffrey-davidson`, on server `cold-moon`
(753072):

| Site | Forge site ID | Site root |
| --- | --- | --- |
| Staging | 3366565 | `/home/forge/staging.thelaravelarchitect.com` |
| Production | 3044519 | `/home/forge/thelaravelarchitect.com` |

Both sites deploy branch `main`, with Forge push-to-deploy **off** (disabled on
2026-09-19). CI triggers staging only after tests pass; production uses the
manual promotion workflow (see [the release process](../releases.md)). Do not
manually redeploy staging while it is being reviewed or promoted. A one-off
operational deployment must use the same revision pinning and approval
requirements.

Each site holds its own copy of the pinned [Forge deploy script](forge-deploy-script.md).
Production script or Nginx changes require immediate confirmation of the target.

The two sites share one small server; see
[Scheduled tasks](scheduled-tasks.md#production-only-scheduled-tasks) for its
size and why some tasks run only on production.

## GitHub environments and secrets

The GitHub environments `staging` and `production` allow deployments from
`main` only. Production requires Jeffrey's review and permits self-review, so a
single-person operator can dispatch and approve their own deployment. Both
environments disallow administrator bypass. The staged-release workflows run
only while the repository variable `STAGED_RELEASES_ENABLED` is `true`.

- `FORGE_DEPLOY_HOOK`: each environment stores its own site's existing Forge
  deploy hook. Never use a production hook in staging or a broad account API
  token when the site hook is sufficient. Supplying these secrets grants CI
  deployment authority and requires explicit operator approval.
- `CF_ACCESS_CLIENT_ID` and `CF_ACCESS_CLIENT_SECRET`: a Cloudflare Access
  service token authorized only for the staging application. Both environments
  store it, because production promotion needs read access to staging to
  revalidate the approved candidate. Do not make staging public. Credential
  creation and access-policy changes require separate approval; never paste
  tokens into chat, logs, repository files or workflow inputs.

Deployment goes through these per-site hooks only. The repository contains no
direct Forge API client or `/api/v1` request, and application deployment must
not depend on undocumented Forge API v1 requests.

## Cloudflare and Nginx

Both sites sit behind Cloudflare. On the server, Forge's Nginx configuration
uses the `real_ip` module with Cloudflare's published IP ranges to restore each
visitor's address from Cloudflare's header (verified on production in October
2026). PHP therefore already sees the real client IP, and the application
trusts no proxies.

A new or rebuilt server must get the same Nginx `real_ip` configuration before
it takes traffic. Without it every request appears to come from a Cloudflare
edge, and the IP-keyed rate limits (contact form, newsletter, search,
unsubscribe, Resend webhook) would throttle unrelated visitors together.

### Revision marker

Each site's server block has an exact Nginx location for the revision marker.
When adding it, preserve the existing configuration and validate it before
reload:

```nginx
location = /deployment.json {
    try_files $uri =404;
    add_header Cache-Control "no-store" always;
}
```

Bypass Cloudflare caching for `/deployment.json` and `/up`. The marker exposes
only a source revision and Forge deployment ID, never environment values.

### robots.txt on production

The application generates `/robots.txt` (see
[Public site and SEO](../architecture/public-site-and-seo.md#robotstxt)). Do not
add a static `public/robots.txt`, because the web server would serve it before
Laravel.

On production, Cloudflare currently answers `/robots.txt` itself with its
managed "content signals" comment block (no rules, no `Sitemap:` line), even
though Bot Preference Sync is off and no rule or Worker matches the path. The
same route arrives intact at `/index.php/robots.txt`. Making the response
cookie-free and cacheable did not change this, so the cause is a
Cloudflare-side setting (support ticket pending).

After a deploy, check the application's file at `/index.php/robots.txt` and the
public file at `/robots.txt`. Treat a public file with no `User-agent` line as
this Cloudflare problem, not an application fault. Until it is resolved, submit
`/sitemap.xml` in Search Console so sitemap discovery does not depend on
robots.txt.

## Application settings

- `APP_URL`: with `APP_ENV=production`, every absolute URL, including
  newsletter confirmation links, is built from `APP_URL` instead of the
  request's Host header. Set it to each site's canonical HTTPS address (the
  staging address on staging).
- `TLA_DEPLOYMENT_ENVIRONMENT`: `production` or `staging`, matching
  `SENTRY_ENVIRONMENT` (see [Observability](observability.md)). Staging also
  runs with `APP_ENV=production`, so this setting is what tells the two apart:
  it gates the production-only scheduled tasks and decides what `/robots.txt`
  allows. It falls back to `APP_ENV` when unset, so staging must set it.
- Do not reuse Mouse28 tokens, DSNs, or projects.

## Staging runtime

Staging needs:

- isolated persistent SQLite and storage, with the database queue and cache
  tables;
- `QUEUE_CONNECTION=database` and `CACHE_STORE=database`;
- `BACKUP_MEDIA_PATH` pointing to staging's own persistent
  `storage/app/public`;
- `MAIL_MAILER=log` or an approved sandbox. Staging never sends email, never
  receives production subscribers and needs no Resend webhook;
- no production backup or storage write credentials. Staging takes no scheduled
  backups (see [Scheduled tasks](scheduled-tasks.md));
- `TLA_DEPLOYMENT_ENVIRONMENT=staging`, set before the scheduler is enabled.
  Backups, YouTube jobs and media checks run only when it is `production`, while
  the heartbeat and pruning tasks run on both sites.

Staging has one Forge database queue worker (`default` queue, timeout 60
seconds, tries 3) and a per-minute scheduler against the staging `current`
directory. Keep the timeout below the queue's 90-second retry interval. Preserve
the working Nightwatch agent; do not add a duplicate.

To turn on runtime health on a fresh staging site, refresh its configuration,
observe both fresh heartbeats, then set `RUNTIME_HEALTH_ENABLED=true` and
refresh the configuration again.
