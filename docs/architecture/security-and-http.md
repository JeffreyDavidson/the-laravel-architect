# Security and HTTP

Headers, rate limits, signed URLs, error pages, the health endpoint, and the
safeguards that differ between production and other environments.

## Response headers

Application responses set a constrained Content Security Policy plus
cross-origin isolation, clickjacking, transport-security, MIME-sniffing,
referrer, and browser-feature policy headers globally.

- Public scripts use a per-request nonce instead of `unsafe-inline` or
  `unsafe-eval`; the Filament admin path retains those allowances for framework
  compatibility.
- The CSP `frame-src` allows `https://share.transistor.fm` and YouTube, not
  Spotify or Apple.
- Admin, preview, and all 4xx/5xx responses also send
  `Cache-Control: no-store, private` and `X-Robots-Tag: noindex, nofollow`.
- `AddSecurityHeaders::apply()` is registered with
  `withExceptions(...)->respond(...)` so responses rendered before the
  middleware runs, such as maintenance mode, carry the same headers.

## Rate limits

Contact and newsletter submissions include abuse controls. The limiters are all
keyed by IP address, which relies on the Nginx `real_ip` setup described in
[Environments](../operations/environments.md#cloudflare-and-nginx).

| Limiter | Limit | Details |
| --- | --- | --- |
| `contact-form` | 3 sent messages an hour, and 10 attempts a minute | [Contact](contact.md#abuse-controls) |
| `newsletter` | 5 sign-ups an hour | [Newsletter](newsletter.md#signing-up) |
| `newsletter-confirm` | 10 requests a minute | [Newsletter](newsletter.md#confirming) |
| `newsletter-unsubscribe` | 120 a minute | [Newsletter](newsletter.md#unsubscribing) |
| `search` | 30 queries a minute | Below |
| Resend webhook | 60 requests a minute | [Webhook runbook](../operations/runbooks/resend-webhook.md#how-the-endpoint-is-protected) |

Site search is limited through the `search` rate limiter; the empty search page
is never limited, and throttled visitors receive the standalone branded 429
page. Blog search is intentionally unthrottled because it matches only titles,
excerpts, and tag names, and its live updates share Livewire's update endpoint
with the admin panel.

## Signed routes

Signed routes (previews and unsubscribe links) check the signature before route
model binding, so an unsigned or altered link gets the same 403 whether or not
its draft or subscriber exists. Newsletter confirmation links are checked by
`EnsureValidNewsletterConfirmationLink` and redirect instead (see
[Newsletter](newsletter.md#confirming)).

## Error pages

Refused requests, such as unsigned or expired preview links and altered
unsubscribe links, get the standalone branded 403 page (`errors/403`). It is
built on the same `errors.server` template as the 429, 500 and 503 pages and
offers only a link home.

## Health endpoint

The `/up` health endpoint verifies the Laravel runtime and access to the
migrated application database and, when runtime health checks are enabled
(`health.runtime.enabled`), that the scheduler and queue heartbeats are fresh.
Production monitoring should treat any non-200 response as unhealthy (see
[Observability](../operations/observability.md)).

## Production safeguards

Outside production, Eloquent rejects lazy loading on models retrieved in
multi-record results, so a missing eager load fails tests and local requests
instead of silently adding N+1 queries; production keeps lazy loading enabled.

In production, which includes staging because it runs with
`APP_ENV=production`, Laravel refuses `migrate:fresh`, `migrate:refresh`,
`migrate:reset`, `migrate:rollback`, and `db:wipe`. Recover databases from
validated snapshots as described in
[Restore and rollback](../operations/restore-and-rollback.md).

For the same reason, scheduled tasks that must not run on staging (backups,
YouTube jobs and media checks) are gated in `routes/console.php` on
`app.deployment_environment` (`TLA_DEPLOYMENT_ENVIRONMENT`) rather than the
scheduler's `environments()` filter; see
[Scheduled tasks](../operations/scheduled-tasks.md#how-the-gate-works).
