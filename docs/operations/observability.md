# Observability

Production uses Nightwatch for server-side telemetry and deployment tracking,
and Sentry for exception reporting on both sites. Availability is watched by the
Forge deployment health check and the `Production uptime` workflow. Who owns
each check is in the [operations index](../operations.md#ownership-of-operational-checks).

## Where it is wired

`App\Providers\MonitoringServiceProvider` registers the Sentry event and
breadcrumb redaction callbacks and the Nightwatch user resolver and redaction
callbacks described below; the classes are in `app/Support/Monitoring`. Sampling rates come from `config/sentry.php` and
`config/nightwatch.php`. The `/up` checks (a migrations-table read and, when
`RUNTIME_HEALTH_ENABLED` is on, the scheduler and queue heartbeats) run in
`App\Listeners\CheckApplicationHealthListener`, which Laravel's event discovery
registers for `DiagnosingHealth`; register it nowhere else, or it runs twice.

## Environments

Staging verification does not require a Nightwatch agent. Telescope is intended
for staging inspection but is not installed or configured by this repository; do
not assume a Telescope dashboard is available. Until that separate installation
and access-control work is completed, use configured Sentry exception reporting
and Forge/application logs for staging diagnosis.

Use one Sentry project for The Laravel Architect and label events with
`SENTRY_ENVIRONMENT=production` or `SENTRY_ENVIRONMENT=staging`. Set
`TLA_DEPLOYMENT_ENVIRONMENT` to the same value (see
[Environments](environments.md#application-settings)). Do not reuse Mouse28
tokens, DSNs, or projects.

## Sentry

Sentry is limited to exception reporting until a separate performance and
privacy review approves broader collection:

```dotenv
SENTRY_LARAVEL_DSN=<project DSN>
SENTRY_ENVIRONMENT=<production-or-staging>
SENTRY_RELEASE=<immutable deployment commit>
SENTRY_TRACES_SAMPLE_RATE=0.0
SENTRY_PROFILES_SAMPLE_RATE=0.0
SENTRY_SEND_DEFAULT_PII=false
```

`SENTRY_RELEASE` falls back to Forge's `FORGE_DEPLOY_COMMIT`, but an explicit
value may be used when verifying an environment outside a Forge deployment.
Never print the DSN in deployment logs or diagnostics.

The application fixes `sentry.max_request_body_size` to `never`, and production
verification enforces it. Container-resolved event and breadcrumb callbacks also
filter request data, query strings, sensitive keyed values, email addresses, and
token-bearing URL paths. They preserve exception classes and stack locations for
diagnosis. These targeted filters do not make arbitrary free-form application
logs safe to populate with personal data or credentials.

## Nightwatch

Nightwatch is required only in production. In the Nightwatch dashboard, create
the production application environment, then use Forge's built-in Nightwatch
integration from the production site's Overview tab. Supply the production token
through Forge, enable monitoring, and set these values:

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

Never store the token in the repository. Leave the ingest URI, timeouts, event
buffer, and server identifier at the package defaults unless the Forge
integration requires an explicit override. The production verifier requires a
nonempty server identifier, positive ingest timeouts, and a positive event
buffer.

The Forge integration manages the required application-specific agent process.
Do not add a second manual process for the same site. If the built-in
integration is unavailable, add one Forge background process named `Nightwatch`
that runs `php artisan nightwatch:agent` from the site directory with one
process and a 15-second graceful shutdown.

After enabling or changing Nightwatch, refresh the application's cached
configuration and run `php artisan nightwatch:status`. Require a successful
status before considering monitoring operational.

### Sampling

Keep request sampling at 10% initially. Production verification requires a
positive rate no higher than 10%, and a lower rate may be used after reviewing
event volume. Command, exception, and scheduled-task sampling must remain
positive and no higher than 100%.

### Privacy

Request payload, request-header, exception source-code, mail-event, and
application-log capture must remain disabled unless a separate privacy review
approves them. The production verifier also prevents required payload and
header redactions from being removed. Contact-mail subjects and free-form log
messages or context can contain personal or secret values. The configured
`nightwatch` log channel intentionally uses a null handler, even if it is
accidentally added to `LOG_STACK`.

What the redaction keeps:

- **Requests:** URLs and execution previews replace complete paths and IP
  addresses with Laravel route templates, so newsletter tokens and signed URL
  signatures are not ingested by request or child-event telemetry. Unmatched
  routes use a fixed placeholder.
- **Outgoing requests:** only each URL's scheme and host; user information,
  ports, paths, query strings, and fragments are removed.
- **Commands:** only the registered command name, so arguments and options are
  not ingested.
- **Cache keys and authenticated user IDs:** replaced with application-keyed
  HMAC digests, preserving correlation without exposing source values.
- **Queries:** SQL structure, with raw string and numeric literals and comments
  replaced.
- **Exceptions:** class, code, file, line, and a type-only stack trace; all
  free-form messages and unhandled-exception previews are replaced.

Do not restore Nightwatch's default user details or raw identifiers without the
same privacy review.

### Nightwatch deployment tracking

Forge exposes the immutable release commit as `FORGE_DEPLOY_COMMIT`. Run
Nightwatch's deployment command from the new release after its caches and assets
are ready. The [Forge deploy script](forge-deploy-script.md) sends this
deployment marker after migrating and just before activating the release,
without letting a failure stop the deploy:

```bash
php artisan nightwatch:deploy "$FORGE_DEPLOY_COMMIT" --ref="$FORGE_DEPLOY_COMMIT"
```

The application configuration falls back to the same Forge value for
`nightwatch.deployment`. Run `php artisan app:verify-deployment "$FORGE_DEPLOY_COMMIT"`
afterward; it fails if the application is reporting a different Nightwatch
deployment identifier. The package's deployment command reports API failures in
its output but currently exits successfully, so confirm that the matching
deployment marker appears in the Nightwatch dashboard rather than relying on its
exit code alone.

### Nightwatch dashboard baseline

After production telemetry is visible, configure the dashboard to notify the
operational recipient for any unhandled exception, failed queued job, and failed
scheduled task. Establish slow-request and slow-query thresholds from observed
production baselines instead of arbitrary local timings. Review sampled request
volume after the first full traffic cycle and lower the request rate when the
retained data is sufficient; do not raise it above 10% without a cost and
privacy review.

After the first deployment and after material Nightwatch configuration changes,
confirm that:

- the expected deployment marker and server identifier are visible;
- sampled requests use route templates and contain no IP address, headers,
  payload, or route parameter values;
- exceptions, SQL, cache keys, user identifiers, commands, and outgoing URLs
  retain only their documented redacted forms;
- queued jobs, scheduled tasks, and notifications are arriving without message
  bodies or recipient details;
- mail and application-log events are absent;
- each configured alert reaches the monitored operational destination.

Nightwatch reports new failed jobs; how long they are kept is in
[Scheduled tasks](scheduled-tasks.md#failed-job-retention).

## Forge deployment health check

The production site's Forge deployment health check was enabled on 2026-09-19
with `https://thelaravelarchitect.com/up` as its URL. Keep it enabled and
require HTTP 200 from this endpoint. Reconfirm the setting in Forge when
changing deployment configuration.

Forge owns the external request, while the application owns its database and
heartbeat checks. A running Supervisor process is not proof that queue jobs are
executing, so retain the queued heartbeat. Allow heartbeat initialization after
clearing cache before expecting readiness. See
[Forge deployment health checks](https://laravel.com/forge/docs/sites/deployments#deployment-health-checks).

## Production uptime check

The `Production uptime` workflow runs every ten minutes and on manual dispatch.
It uses only `curl` and `jq` (preinstalled on GitHub-hosted runners), with no
checkout, installed dependencies or secrets. It requires
`https://thelaravelarchitect.com/up` to return HTTP 200 within 20 seconds and
`https://thelaravelarchitect.com/deployment.json` to contain a 40-character
`revision`. A failed check is retried once after 30 seconds before the job
fails. Logs show only status codes and timings.

GitHub emails the repository owner when a scheduled run fails, so keep Actions
failure notifications enabled in GitHub notification settings. GitHub can delay
or skip scheduled runs, and in practice runs them far less often than the cron
asks (about three runs in 22 hours were observed after the workflow was added),
so this check is not a guaranteed or timely alert. An external monitor such as
UptimeRobot or Better Stack watching `/up` is the reliable option for
independent alerting.
