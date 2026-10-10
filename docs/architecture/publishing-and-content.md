# Publishing and content

Posts, projects, podcast episodes and newsletter issues share one publishing
model. This page covers when content is public, how it goes live, how slugs and
deletion work, and the podcast and video specifics.

## When content is public

Scheduled posts and episodes become public when their publication date arrives,
without a scheduler job or a stored status change. Drafts, posts in review,
undated content, and future content remain private; episodes also require an
active podcast. Projects require Published status.

Editorial dashboard counts and publication filters share the model publication
scopes:

- `published()` includes published or scheduled content whose date has arrived.
- `scheduled()` includes either publishable status with a future date.
- `unpublished()` is the complement of `published()`, including missing dates.

These scopes do not rewrite stored editorial statuses. Pipeline publication
links use the publication filter, while the separate status filter retains its
literal editorial meaning. The newsletter issue list comes from creator-kit and
uses tabs instead (All, Drafts, Scheduled, Published and Unpublished, with
counts); the dashboard's newsletter queue links to the Unpublished tab.

The Subscribers and Newsletter issues admin screens, and the "View on site"
action on posts, projects, episodes and issues, come from creator-kit
(`CreatorKitPlugin` in `AdminPanelProvider`). `App\Services\ContentUrls`, bound to
the package's `ContentUrls`, tells the action each record's presenter URL.

## Publishing and unpublishing

Posts, projects, episodes and newsletter issues implement
`App\Contracts\Publishable` (shared with the Mouse28 repository).

- `isPublished()` means live now.
- `isScheduled()` means set to go live at a future date.
- `publish()` and `unpublish()` are the state changes only; they do not check
  readiness.

Content goes live only through the `PublishContentAction` /
`UnpublishContentAction` header actions on each edit page. The publish button
saves the form and calls creator-kit's `PublishContent` action, the only
domain code that calls `publish()`. The publishing status, concerns, actions and
form components below come from `jeffreydavidson/creator-kit`; TLA keeps its
readiness rules and plugs them in through `App\Publishing\ContentPublishingReadiness`.

- `PublishContent` refuses content while a required detail is missing by
  throwing the package's `ContentNotReadyToPublish`, which carries the missing
  checks' labels (`$issues`); the button shows them in a "… is not ready to publish"
  notification. Otherwise it keeps an existing publish date (a future date
  makes it Scheduled) and otherwise uses now.
- Unpublishing returns content to Draft and keeps its date and slug.
- `PublishStatusSelect` offers only pre-publication statuses and shows a live or
  scheduled status locked, so saving the form cannot change it.
- `PublishDatePicker` is required while the status is live or scheduled, so
  clearing the date cannot quietly take that content offline; drafts may leave
  it empty.
- A newsletter issue's publish date is locked once the issue has been sent, so
  the links in delivered emails keep working. Publishing and sending a
  newsletter issue remain separate steps (see [Newsletter](newsletter.md)).

## Readiness

The publishing and readiness rules live in `app/Publishing`, separate from the
models, Filament and HTTP (enforced by
`tests/Architecture/PublishingArchitectureTest.php`).

- `ContentReadiness` is the per-record checklist for posts, projects, podcasts,
  episodes, newsletter issues and videos. Each item is an
  `App\Enums\ReadinessCheck` case, whose label is the text people see. Its
  `publishingIssues()` returns the required-to-publish checks still missing:
  post: content, excerpt, category; project: description, case study; episode:
  podcast, description, media; newsletter issue: content. The rest of the
  checklist is advisory.
- `ContentReadinessCriteria` applies the same checks to a query builder in SQL,
  so lists and counts filter without loading records;
  `ContentReadinessCriteriaTest` proves the PHP and SQL verdicts agree.
  `ProjectReadinessCriteria` applies the projects table's readiness filter
  (`ProjectReadinessFilter`, values `ready`, `needs_image`, `needs_case_study`,
  `needs_details`), and `ContentReadinessSummaryQuery` counts the dashboard's
  "Needs finishing" rows (`ContentReadinessArea`).
- The admin `ReadinessColumn` formats the verdict for display ("4/6 complete ·
  Missing: Featured image, Tags").

## Dates and the display timezone

The application, database and publication comparisons stay in UTC, while people
work in the display timezone `app.display_timezone` (env `APP_DISPLAY_TIMEZONE`,
default `America/New_York`, read through creator-kit's `DisplayTimezone`).

- It is Filament's default timezone, so the shared `PublishDatePicker` and admin
  date columns enter and show times in it.
- Public pages, archive and search results date content through the
  `<x-display-date>` component or `DisplayTimezone::convert()`.
- The archive's year list and year filter and the admin publishing activity
  chart also group by display-timezone year and month (UTC boundaries computed
  per year in PHP, so daylight saving time is handled).
- Machine-readable timestamps (RSS, sitemap, JSON-LD, Open Graph) keep their UTC
  offsets.

## Source review

Posts can record the official source they rely on (`source_url`) and when it was
last checked (`last_reviewed_at`). A sourced post is due for review when it was
never checked or was last checked more than `content.post_review_interval_days`
(env `POST_REVIEW_INTERVAL_DAYS`, default 180) days ago. The rules come from
creator-kit's `HasSourceReview` (`Post` supplies the interval and tracks only
posts with a `source_url`); its `SourceReviewSection`, `SourceReviewColumns` and
`ReviewDueFilter` build the form section, the posts table's Source review column
and the filter, which lists published posts only.

This is separate from the editorial approval fields (`reviewed_by`,
`reviewed_at`, `review_notes`) and is shown only in the admin.

## Slugs

Admin forms generate an initial slug but preserve it when titles change, and
explicit slug edits retain uniqueness validation. Tag slugs are translatable
JSON, so `App\Rules\UniqueTagSlug` checks them in the current locale, and
creator-kit's `NotReservedNewsletterSlug` rejects newsletter issue slugs taken by
static `/newsletter/*` routes such as `rss` and `confirmed`.

Slugs of posts, projects, episodes and newsletter issues lock once their content
has a Published or Scheduled status (`LocksSlugAfterPublication` stamps
`slug_locked_at`, and the migration backfilled content that was already live).
They stay locked when the content is later unpublished, because public URLs have
no redirects. The admin slug field is disabled for locked content and a
submitted value is ignored; there is deliberately no way to unlock a slug in the
admin.

Trashed slugs stay reserved: the slug generator and `unique` validation both
include trashed rows, and the content archive importer restores a trashed record
with the imported slug.

## Trash and permanent deletion

Posts, projects, episodes, newsletter issues and podcasts soft-delete. Deleting
moves them to the trash (hidden from every public page, feed, sitemap, search
and admin count, since those use the default scopes) and keeps their media, SEO
row and tags so a restore is complete.

Only a force delete runs transactionally and removes owned files, responsive
variants, OG caches, the SEO row and tag links (`HasTagsUntilForceDeleted` keeps
spatie/laravel-tags from detaching tags on a soft delete). Filament resources
offer a trashed filter plus Restore and Force delete on the edit page and in
bulk. How owned files are removed is in [Media](media.md#cleanup).

Deleting a podcast trashes its episodes with it once the delete is confirmed.
The episodes it trashes are stamped with the podcast's own `deleted_at` (even
when the delete spans several seconds), so restoring it restores the episodes
trashed with it (same or later `deleted_at`) but not ones trashed separately
before. Force deleting it force deletes all of its episodes with their cleanup.
The whole cascade lives in `PodcastObserver` (`trashed`, `restoring`, and
`deleting` for a force delete) and runs inside the podcast delete's transaction,
so an episode that cannot be deleted rolls the podcast delete back.

## Related posts and episodes

A post can relate to several episodes and an episode to several posts, through
the `episode_post` pivot (set from the post form's Related Episodes select; a
link is removed when either side is permanently deleted). The links are
admin-only for now; a public related-episode link must show only published
episodes.

## Podcasts and episodes

Episode durations are stored in seconds (`episodes.duration_seconds`; the old
`duration_minutes` column was converted and then dropped).

- The public pages round to the nearest minute ("25 min", "1h 5m").
- The episode JSON-LD uses an ISO 8601 duration (`PT1H2M5S`).
- The admin form takes seconds.
- The public content archive exports and imports `duration_seconds` (an archive
  exported before the change carries no usable duration) and `transistor_url`.

Public episode playback is a Transistor player for an episode with a valid
`transistor_url` share link (`https://share.transistor.fm/s/{id}`, shown as the
`/e/{id}` embed: `Episode::transistorEpisodeId()` reads the ID and
`EpisodePresenter::transistorEmbedUrl()` builds the player URL); YouTube is
separate. The custom audio player, hosted and
uploaded audio, and the Spotify and Apple embeds are retired:

- the admin form no longer offers those fields;
- the public page and episode JSON-LD ignore them;
- "Episode media" readiness counts only a Transistor share link or a YouTube
  link;
- their `audio_url`, `audio_path` and `embed_url` columns have been dropped (the
  migration refuses to run while any episode still holds a value in one);
- stored audio files are no longer owned by any record, so any left in
  `episodes/audio/` show up as orphans in the media orphan report.

The CSP `frame-src` allows `https://share.transistor.fm` and YouTube, not Spotify
or Apple. Moving a show to Transistor is a
[runbook](../operations/runbooks/transistor-move.md).

## YouTube videos

YouTube videos are synchronized by two commands, both scheduled in
`routes/console.php` on the production deployment only (see
[Scheduled tasks](../operations/scheduled-tasks.md#production-only-scheduled-tasks)).

- `youtube:stats` runs daily and refreshes the view, like and comment counts of
  every stored video in batches of 50. It reports and exits successfully when
  there are no videos yet.
- `youtube:sync` runs weekly and fetches up to `--limit` (default 50) channel
  videos, updating existing `videos` rows by `youtube_id` and creating new ones.

## Activity log

Content changes are recorded with Spatie Activity Log in the `application` log.
Posts, episodes, podcasts, projects, newsletter issues, and videos record only
editorial attributes: long-form content, synchronized YouTube statistics and
descriptions, and sync timestamps are excluded. A daily `activitylog:clean` run
removes entries older than the package default of 365 days.
