# Synchronizing public production content to staging

Run `php artisan content:sync-production --staging` from the staging release to
replace staging's public content with the current production versions. The flag
permits `APP_ENV=production` only on `staging.thelaravelarchitect.com`; local
environments do not require it.

## What it copies

The command transfers only published posts, projects and newsletter issues,
referenced categories and tags, active podcasts and their published episodes,
published videos, their SEO metadata, and referenced public media. Responsive
image variants are regenerated after media transfer, including same-path
replacements.

Content that is no longer public in production is unpublished in staging, while
staging-only drafts remain intact.

## What it never copies

Synchronization maps posts to a non-login staging content owner and never
exports private project repository URLs, production users, subscribers,
authentication data, review notes, activity logs, failed jobs, cache or session
data, credentials, or environment configuration.

## Safety

Synchronization and archive import share a target guard that always rejects
production hostnames, regardless of `APP_ENV` or the staging flag.
